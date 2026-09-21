<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Read-only report on phone numbers in `users` and `kadi.accounts`: malformed values, blank strings and
 * numbers shared by several accounts. Changes nothing; run it before adding a unique index.
 */
class PhonesAuditCommand extends Command
{
    protected $signature = 'phones:audit {--show-numbers : Print full numbers (default masks all but the last 3 digits)}';

    protected $description = 'Report malformed, blank and duplicate phone numbers (read-only)';

    public function handle(): int
    {
        $this->report('users', $this->userRows());

        try {
            $this->report('kadi.accounts', DB::connection('kadi')->table('accounts')->get(['id', 'email', 'phone'])->all());
        } catch (\Throwable $e) {
            $this->warn('kadi.accounts skipped: '.$e->getMessage());
        }

        return self::SUCCESS;
    }

    /** @return array<int, object> */
    private function userRows(): array
    {
        return User::query()->get(['id', 'email', 'phone'])->map(fn ($u) => (object) [
            'id' => $u->id,
            'email' => $u->email,
            // Raw column value: the accessor-free attribute is what is actually stored.
            'phone' => $u->getRawOriginal('phone'),
        ])->all();
    }

    /** @param  array<int, object>  $rows */
    private function report(string $label, array $rows): void
    {
        $blank = $malformed = $needsNormalising = 0;
        $byNumber = [];

        foreach ($rows as $row) {
            $raw = $row->phone;

            if ($raw === null) {
                continue;
            }

            if (trim((string) $raw) === '') {
                $blank++;

                continue;
            }

            $normalized = PhoneNumber::normalize((string) $raw);

            if (! PhoneNumber::isValid($normalized)) {
                $malformed++;
                $this->line("  malformed  #{$row->id}  ".$this->mask((string) $raw));

                continue;
            }

            if ($normalized !== $raw) {
                $needsNormalising++;
            }

            $byNumber[$normalized][] = $row;
        }

        $duplicates = array_filter($byNumber, fn ($group) => count($group) > 1);

        $this->info("== {$label} (".count($rows).' rows) ==');
        $this->line("  blank strings (should be NULL): {$blank}");
        $this->line("  malformed / not Kenyan:         {$malformed}");
        $this->line("  valid but not 254XXXXXXXXX:     {$needsNormalising}");
        $this->line('  numbers on more than one row:   '.count($duplicates));

        foreach ($duplicates as $number => $group) {
            $ids = implode(', ', array_map(fn ($r) => '#'.$r->id, $group));
            $this->line('    '.$this->mask((string) $number)." -> {$ids}");
        }

        $this->newLine();
    }

    private function mask(string $number): string
    {
        if ($this->option('show-numbers')) {
            return $number;
        }

        return str_repeat('*', max(strlen($number) - 3, 0)).substr($number, -3);
    }
}
