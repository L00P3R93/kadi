<?php

namespace App\Console\Commands;

use App\Facades\KadiApi;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KadiMigrateProfilePics extends Command
{
    protected $signature = 'kadi:migrate-profile-pics
                            {--dry-run : Preview what would be migrated without making changes}
                            {--user= : Migrate a single user by their local user ID}';

    protected $description = 'Re-upload existing profile pictures into per-account subfolders (kadi/images/{account_id}/{pic})';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $userId   = $this->option('user');

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $query = User::whereNotNull('linked_id');
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No linked users found.');
            return self::SUCCESS;
        }

        $this->info("Processing {$users->count()} user(s)...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $migrated = 0;
        $skipped  = 0;
        $failed   = 0;

        foreach ($users as $user) {
            $bar->advance();

            try {
                $customerData = KadiApi::getCustomer($user->linked_id);
                $data         = $customerData['data'] ?? $customerData;

                $pic       = $data['pic'] ?? null;
                $accountId = $data['id'] ?? $user->linked_id;

                if (! $pic) {
                    $skipped++;
                    continue;
                }

                $imageBase  = rtrim(config('services.kadi_api.image_url'), '/');
                $currentUrl = "{$imageBase}/{$pic}";
                $newUrl     = "{$imageBase}/{$accountId}/{$pic}";

                // Skip if the pic field already includes the account ID subfolder
                if (str_contains($pic, '/')) {
                    $this->newLine();
                    $this->line("  <comment>SKIP</comment>  User #{$user->id} (account {$accountId}) — pic already has subfolder: {$pic}");
                    $skipped++;
                    continue;
                }

                if ($isDryRun) {
                    $this->newLine();
                    $this->line("  <info>WOULD MIGRATE</info>  User #{$user->id} (account {$accountId})");
                    $this->line("    From: {$currentUrl}");
                    $this->line("    To:   {$newUrl}");
                    $migrated++;
                    continue;
                }

                // Download the image from the old flat location
                $response = Http::timeout(30)->get($currentUrl);

                if (! $response->successful()) {
                    $this->newLine();
                    $this->warn("  SKIP  User #{$user->id} — could not download image from {$currentUrl} (HTTP {$response->status()})");
                    $skipped++;
                    continue;
                }

                // Write to a temp file
                $ext      = pathinfo($pic, PATHINFO_EXTENSION) ?: 'jpg';
                $tmpPath  = sys_get_temp_dir().'/kadi_migrate_'.$accountId.'_'.time().'.'.$ext;
                file_put_contents($tmpPath, $response->body());

                try {
                    KadiApi::uploadProfilePic($user->linked_id, $tmpPath, $pic);
                    $migrated++;
                    $this->newLine();
                    $this->line("  <info>OK</info>  User #{$user->id} (account {$accountId}) → {$newUrl}");
                } finally {
                    @unlink($tmpPath);
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("  FAIL  User #{$user->id}: {$e->getMessage()}");
                Log::error("kadi:migrate-profile-pics failed for user #{$user->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Result', 'Count'],
            [
                [$isDryRun ? 'Would migrate' : 'Migrated', $migrated],
                ['Skipped',  $skipped],
                ['Failed',   $failed],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
