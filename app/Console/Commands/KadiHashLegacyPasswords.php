<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * One-off remediation for audit finding C-1: the legacy kadi.accounts table
 * historically stored registration passwords in PLAINTEXT. This command
 * converts every plaintext row to a bcrypt hash in place, preserving login
 * parity once the game side verifies with password_verify().
 *
 * Rows already holding bcrypt hashes ($2y$/$2a$/$2b$ prefix) or NULL are left
 * untouched. Run with --dry-run first; without it, changes are committed in
 * chunks and logged.
 */
class KadiHashLegacyPasswords extends Command
{
    protected $signature = 'kadi:hash-legacy-passwords
                            {--dry-run : Preview affected rows without writing}
                            {--chunk=200 : Number of rows per update batch}';

    protected $description = 'Convert plaintext passwords in kadi.accounts to bcrypt hashes (audit C-1 remediation)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));

        if (! $dryRun && ! $this->confirm('This will irreversibly rewrite kadi.accounts.password values as bcrypt hashes. Continue?')) {
            return self::FAILURE;
        }

        $query = DB::connection('kadi')
            ->table('accounts')
            ->whereNotNull('password')
            ->where('password', '!=', '')
            ->where(function ($q) {
                $q->where('password', 'not like', '$2y$%')
                    ->where('password', 'not like', '$2a$%')
                    ->where('password', 'not like', '$2b$%');
            });

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No plaintext password rows found. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info($dryRun
            ? "DRY RUN — {$total} row(s) would be hashed."
            : "Hashing {$total} row(s)...");

        $hashed = 0;
        $failed = 0;

        $query->orderBy('id')->chunk($chunkSize, function ($rows) use (&$hashed, &$failed, $dryRun) {
            foreach ($rows as $row) {
                try {
                    // Hash BEFORE clearing the value from memory of subsequent rows.
                    $hash = Hash::make((string) $row->password);

                    if (! $dryRun) {
                        DB::connection('kadi')
                            ->table('accounts')
                            ->where('id', $row->id)
                            ->update(['password' => $hash]);
                    }

                    $hashed++;
                } catch (Throwable $e) {
                    $failed++;
                    $this->error("Row #{$row->id}: ".$e->getMessage());
                }
            }

            if (! $dryRun) {
                $this->line("  processed {$hashed}/...");
            }
        });

        $this->info("Done. Hashed: {$hashed}, Failed: {$failed}".($dryRun ? ' (dry run — nothing written)' : ''));

        if ($failed > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
