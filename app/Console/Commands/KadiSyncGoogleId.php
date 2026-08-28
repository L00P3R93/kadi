<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class KadiSyncGoogleId extends Command
{
    protected $signature = 'kadi:sync-google-id
                            {--dry-run : Preview affected rows without writing}
                            {--chunk=200 : Number of rows per update batch}';

    protected $description = 'Sync kadi.accounts.google_id to kadi_main.users.account_no where they differ';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));

        $emails = DB::table('users')
            ->whereNotNull('account_no')
            ->where('account_no', '!=', '')
            ->pluck('account_no', 'email')
            ->filter();

        if ($emails->isEmpty()) {
            $this->info('No users with account_no found. Nothing to do.');

            return self::SUCCESS;
        }

        $query = DB::connection('kadi')
            ->table('accounts')
            ->whereIn('email', $emails->keys())
            ->where(function ($q) use ($emails) {
                foreach ($emails as $email => $accountNo) {
                    $q->orWhere(function ($sub) use ($email, $accountNo) {
                        $sub->where('email', $email)
                            ->where(function ($inner) use ($accountNo) {
                                $inner->whereNull('google_id')
                                    ->orWhere('google_id', '!=', $accountNo);
                            });
                    });
                }
            });

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('All google_id values are already in sync. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info($dryRun
            ? "DRY RUN — {$total} row(s) would be updated."
            : "Syncing google_id for {$total} row(s)...");

        $updated = 0;
        $failed = 0;

        $query->orderBy('id')->chunk($chunkSize, function ($rows) use ($emails, &$updated, &$failed, $dryRun) {
            foreach ($rows as $row) {
                try {
                    $accountNo = $emails[$row->email] ?? null;

                    if ($accountNo === null || $row->google_id === $accountNo) {
                        continue;
                    }

                    if (! $dryRun) {
                        DB::connection('kadi')
                            ->table('accounts')
                            ->where('id', $row->id)
                            ->update(['google_id' => $accountNo]);
                    }

                    $updated++;
                } catch (Throwable $e) {
                    $failed++;
                    $this->error("Row #{$row->id}: ".$e->getMessage());
                }
            }

            if (! $dryRun) {
                $this->line("  processed {$updated}/...");
            }
        });

        $this->info("Done. Updated: {$updated}, Failed: {$failed}".($dryRun ? ' (dry run — nothing written)' : ''));

        if ($failed > 0) {
            Log::error("kadi:sync-google-id completed with {$failed} failures");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
