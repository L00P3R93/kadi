<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Number;

#[Signature('logs:clear
{names?* : Log names to clear, for example "laravel". Matches laravel.log and rotated laravel-YYYY-MM-DD.log}
{--all : Clear every application log (dev-tool logs such as browser.log are skipped)}
{--dry-run : List what would be cleared and change nothing}
{--force : Do not ask for confirmation}')]
#[Description('Empty application log files in storage/logs (the files are truncated, never deleted).')]
class ClearLogs extends Command
{
    use ConfirmableTrait;

    /**
     * Files `--all` never touches: written by dev tooling (Laravel Boost's browser-logs tool), not by
     * the application. They can still be cleared by naming them: `logs:clear browser`.
     *
     * @var list<string>
     */
    private const EXCLUDED_FROM_ALL = ['browser.log'];

    public function handle(): int
    {
        $names = array_values(array_filter((array) $this->argument('names')));
        $all = (bool) $this->option('all');

        if ($names === [] && ! $all) {
            $this->error('Nothing to do: name the logs to clear (for example "logs:clear laravel"), or pass --all.');

            return self::FAILURE;
        }

        if ($names !== [] && $all) {
            $this->error('Use either log names or --all, not both.');

            return self::FAILURE;
        }

        $files = $all ? $this->allLogs() : $this->namedLogs($names);

        if ($files === null) {
            return self::FAILURE;
        }

        if ($files === []) {
            $this->warn('No matching log files.');

            return self::SUCCESS;
        }

        $total = array_sum(array_map(fn (string $file) => (int) File::size($file), $files));

        $this->table(['File', 'Size'], array_map(fn (string $file) => [basename($file), Number::fileSize((int) File::size($file))], $files));

        if ($this->option('dry-run')) {
            $this->info('Dry run: '.count($files).' file(s), '.Number::fileSize($total).'. Nothing was changed.');

            return self::SUCCESS;
        }

        // Always ask (not only in production) unless --force: emptying a log cannot be undone. This is
        // the same confirmation mechanism as migrate:fresh, with the "only in production" test removed.
        if (! $this->confirmToProceed('This will empty '.count($files).' log file(s) ('.Number::fileSize($total).').', fn () => true)) {
            return self::FAILURE;
        }

        $failed = 0;

        foreach ($files as $file) {
            try {
                File::put($file, '');
            } catch (\Throwable $e) {
                $this->error('Could not clear '.basename($file).': '.$e->getMessage());
                $failed++;
            }
        }

        $this->info('Cleared '.(count($files) - $failed).' of '.count($files).' file(s), freeing up to '.Number::fileSize($total).'.');

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Every `*.log` file in storage/logs except the dev-tool ones.
     *
     * @return list<string>
     */
    private function allLogs(): array
    {
        return array_values(array_filter(
            $this->logFiles(),
            fn (string $file) => ! in_array(basename($file), self::EXCLUDED_FROM_ALL, true),
        ));
    }

    /**
     * `laravel` matches laravel.log and the rotated laravel-2026-09-19.log a `daily` channel writes.
     *
     * @param  list<string>  $names
     * @return list<string>|null null when a name is not valid
     */
    private function namedLogs(array $names): ?array
    {
        $matched = [];

        foreach ($names as $name) {
            // A bare name only: no paths, no wildcards, no ".log". Keeps this inside storage/logs.
            if (! preg_match('/^[A-Za-z0-9_\-]+$/', $name)) {
                $this->error("\"{$name}\" is not a valid log name. Use just the name, like \"laravel\".");

                return null;
            }

            $found = array_filter(
                $this->logFiles(),
                fn (string $file) => preg_match('/^'.preg_quote($name, '/').'(-\d{4}-\d{2}-\d{2})?\.log$/', basename($file)) === 1,
            );

            if ($found === []) {
                $this->warn("No log files found for \"{$name}\".");
            }

            $matched = [...$matched, ...$found];
        }

        return array_values(array_unique($matched));
    }

    /**
     * @return list<string>
     */
    private function logFiles(): array
    {
        return array_values(array_filter(
            File::glob(storage_path('logs/*.log')) ?: [],
            fn (string $path) => is_file($path),
        ));
    }
}
