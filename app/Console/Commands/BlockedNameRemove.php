<?php

namespace App\Console\Commands;

use App\Models\BlockedName;
use App\Support\NameGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('blocked-names:remove {terms* : One or more entries to remove}')]
#[Description('Remove names from the blocklist.')]
class BlockedNameRemove extends Command
{
    public function handle(): int
    {
        $status = self::SUCCESS;

        foreach ((array) $this->argument('terms') as $raw) {
            $term = NameGuard::normalise((string) $raw);
            $blocked = BlockedName::where('term', $term)->first();

            if ($blocked === null) {
                $this->warn("\"{$raw}\" is not on the list.");
                $status = self::FAILURE;

                continue;
            }

            $blocked->delete();
            $this->info("Removed \"{$term}\".");
        }

        return $status;
    }
}
