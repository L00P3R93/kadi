<?php

namespace App\Console\Commands;

use App\Facades\BugsApi;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterBugsApi extends Command
{
    protected $signature = 'kadi:register-bugs-api
                            {--dry-run : Preview what would be registered without making changes}
                            {--user= : Process a single user by their local user ID}';

    protected $description = 'Register verified users with a linked_id into the BugsApi and store the returned bugs_id';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $userId = $this->option('user');

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $query = User::whereNotNull('email_verified_at')
            ->whereNotNull('linked_id')
            ->whereNull('bugs_id');

        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No users found matching criteria.');

            return self::SUCCESS;
        }

        $this->info("Processing {$users->count()} user(s)...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($users as $user) {
            $bar->advance();

            try {
                $userArr = [
                    'account_no' => $user->account_no,
                    'name' => $name = $user->name,
                    'username' => Str::slug($name),
                    'email' => $email = $user->email,
                    'phone' => $user->phone ?? null,
                    'password' => Hash::make(Str::lower($email)),
                    'linked_id' => $user->linked_id,
                ];

                if ($isDryRun) {
                    $this->newLine();
                    $this->line("  <info>WOULD REGISTER</info>  User #{$user->id} ({$user->email})");
                    $registered++;

                    continue;
                }

                $response = BugsApi::registerUser($userArr);

                if (isset($response['user_id'])) {
                    $user->update(['bugs_id' => $response['user_id']]);
                    $registered++;
                } else {
                    $this->newLine();
                    $this->warn("  SKIP  User #{$user->id} ({$user->email}) — no user_id in response");
                    $skipped++;
                }
            } catch (RequestException|ConnectionException $e) {
                $failed++;
                $this->newLine();
                $this->error("  FAIL  User #{$user->id} ({$user->email}): {$e->getMessage()}");
                Log::error("kadi:register-bugs-api failed for user #{$user->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Result', 'Count'],
            [
                [$isDryRun ? 'Would register' : 'Registered', $registered],
                ['Skipped', $skipped],
                ['Failed', $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
