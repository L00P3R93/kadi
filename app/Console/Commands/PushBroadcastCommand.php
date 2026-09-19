<?php

namespace App\Console\Commands;

use App\Http\Requests\SendPushBroadcastRequest;
use App\Models\PushBroadcast;
use App\Push\BroadcastLimitExceeded;
use App\Push\PushBroadcaster;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('push:broadcast
    {--title= : Notification title (max 65 characters)}
    {--body= : Notification text (max 240 characters)}
    {--url=/ : Site path opened when tapped, e.g. /wallet}
    {--tag= : Optional tag; a newer notification with the same tag replaces the older one}
    {--urgency=normal : very-low, low, normal or high (high wakes sleeping phones: use it for time-sensitive announcements)}
    {--ttl= : Seconds a push service may hold it for an offline device}
    {--dry-run : Only show how many devices would receive it}
    {--cancel= : Cancel the broadcast with this id instead of sending one}
    {--status= : Show the progress of the broadcast with this id}
    {--force : Ignore the frequency caps (never available through the API)}
    {--yes : Do not ask for confirmation}')]
#[Description('Send a system announcement to every registered device (or --dry-run / --status= / --cancel=).')]
class PushBroadcastCommand extends Command
{
    public function handle(PushBroadcaster $broadcaster): int
    {
        if ($id = $this->option('status')) {
            return $this->showStatus((string) $id);
        }

        if ($id = $this->option('cancel')) {
            return $this->cancel($broadcaster, (string) $id);
        }

        $data = array_filter([
            'title' => trim((string) $this->option('title')),
            'body' => trim((string) $this->option('body')),
            'url' => $this->option('url'),
            'tag' => $this->option('tag'),
            'urgency' => $this->option('urgency'),
            'ttl' => $this->option('ttl') !== null ? (int) $this->option('ttl') : null,
        ], fn ($value) => $value !== null && $value !== '');

        // The very same rules the API enforces.
        $rules = (new SendPushBroadcastRequest)->rules();
        unset($rules['idempotency_key'], $rules['dry_run']);
        $validator = Validator::make($data, $rules, (new SendPushBroadcastRequest)->messages());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::INVALID;
        }

        $devices = $broadcaster->audienceSize();

        if ($this->option('dry-run')) {
            $this->info("Dry run: this would reach {$devices} registered devices. Nothing was sent.");

            return self::SUCCESS;
        }

        if ($devices === 0) {
            $this->warn('There are no registered devices, so there is nobody to notify.');

            return self::SUCCESS;
        }

        $this->line("Title: {$data['title']}");
        $this->line("Body:  {$data['body']}");

        if (! $this->option('yes') && ! $this->confirm("Send this to {$devices} devices?", false)) {
            $this->warn('Cancelled. Nothing was sent.');

            return self::SUCCESS;
        }

        try {
            $broadcast = $broadcaster->create($data, 'cli', null, enforceLimits: ! $this->option('force'));
        } catch (BroadcastLimitExceeded $e) {
            $this->error($e->getMessage().' Try again in '.$e->retryAfter.'s, or pass --force.');

            return self::FAILURE;
        }

        $this->info("Broadcast {$broadcast->id} queued for {$devices} devices. A queue worker must be running.");
        $this->line("Progress: php artisan push:broadcast --status={$broadcast->id}");

        return self::SUCCESS;
    }

    private function showStatus(string $id): int
    {
        $broadcast = PushBroadcast::find($id);

        if ($broadcast === null) {
            $this->error('No broadcast with that id.');

            return self::FAILURE;
        }

        $this->table(['Field', 'Value'], collect($broadcast->toStatusArray())->map(fn ($value, $key) => [$key, (string) $value])->values()->all());

        return self::SUCCESS;
    }

    private function cancel(PushBroadcaster $broadcaster, string $id): int
    {
        $broadcast = PushBroadcast::find($id);

        if ($broadcast === null) {
            $this->error('No broadcast with that id.');

            return self::FAILURE;
        }

        if (! $broadcaster->cancel($broadcast)) {
            $this->warn('That broadcast has already finished, so it cannot be cancelled.');

            return self::FAILURE;
        }

        $this->info('Cancelled. Chunks already delivered cannot be recalled; the rest will not be sent.');

        return self::SUCCESS;
    }
}
