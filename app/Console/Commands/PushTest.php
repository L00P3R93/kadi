<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PushTestSender;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('push:test
{user? : User id or email. Defaults to the first user with a registered device}
{--message= : Text for the notification body}')]
#[Description('Send a real test web push to a user\'s registered devices and report the outcome per device.')]
class PushTest extends Command
{
    public function handle(PushTestSender $sender): int
    {
        if (! $sender->configured()) {
            $this->error('VAPID keys are not set. Run `php artisan webpush:vapid` and set VAPID_SUBJECT in .env.');

            return self::FAILURE;
        }

        $user = $this->resolveUser();

        if (! $user) {
            $this->error($this->argument('user')
                ? 'No user matches "'.$this->argument('user').'".'
                : 'No user has a registered device yet. Turn notifications on first (Profile > Notifications).');

            return self::FAILURE;
        }

        $this->line("Sending a test push to user #{$user->id} ({$user->name})...");

        try {
            $result = $sender->send($user, $this->option('message'));
        } catch (Throwable $e) {
            $this->error('The push could not be sent: '.$e->getMessage());

            if (str_contains(strtolower($e->getMessage()), 'unable to create the key') || str_contains(strtolower($e->getMessage()), 'openssl')) {
                $this->newLine();
                $this->warn('This usually means OpenSSL cannot find its config file (common on Windows/Herd).');
                $this->line('Set OPENSSL_CONF to your PHP openssl.cnf, e.g. <comment>C:\Users\<you>\.config\herd\bin\php84\extras\ssl\openssl.cnf</comment>, then run this again.');
            }

            return self::FAILURE;
        }

        if ($result['devices'] === 0) {
            $this->warn("User #{$user->id} has no registered device. They need to turn notifications on first (Profile > Notifications).");

            return self::FAILURE;
        }

        $this->table(
            ['Devices', 'Delivered', 'Expired (removed)', 'Failed'],
            [[$result['devices'], $result['delivered'], $result['expired'], $result['failed']]],
        );

        if ($result['delivered'] > 0) {
            $this->info('Accepted by the push service. It should appear on the device within a few seconds.');

            return self::SUCCESS;
        }

        $this->error($result['expired'] > 0
            ? 'Every subscription had expired and was removed. Turn notifications off and on again on the device.'
            : 'The push service rejected the message. Check storage/logs for the status code.');

        return self::FAILURE;
    }

    private function resolveUser(): ?User
    {
        $identifier = $this->argument('user');

        if ($identifier === null || $identifier === '') {
            return User::whereHas('pushSubscriptions')->orderBy('id')->first();
        }

        return ctype_digit((string) $identifier)
            ? User::find((int) $identifier)
            : User::where('email', $identifier)->first();
    }
}
