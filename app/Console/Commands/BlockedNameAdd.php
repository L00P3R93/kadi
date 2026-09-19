<?php

namespace App\Console\Commands;

use App\Models\BlockedName;
use App\Support\NameGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('blocked-names:add
    {terms* : One or more names or words players may not use}
    {--contains : Also block names that merely contain the term (blunt: use for long, unambiguous terms)}
    {--reason= : A note for other operators}')]
#[Description('Add names to the blocklist checked at registration and when a player changes their name.')]
class BlockedNameAdd extends Command
{
    public function handle(): int
    {
        $match = $this->option('contains') ? BlockedName::CONTAINS : BlockedName::WORD;

        foreach ((array) $this->argument('terms') as $raw) {
            $term = NameGuard::normalise((string) $raw);

            if ($term === '') {
                $this->warn("Skipped \"{$raw}\": it has no letters, so it could never match a name.");

                continue;
            }

            $blocked = BlockedName::updateOrCreate(['term' => $term], array_filter(['match' => $match, 'reason' => $this->option('reason')]));

            $this->info(($blocked->wasRecentlyCreated ? 'Added' : 'Updated')." \"{$term}\" ({$match}).");
        }

        $this->line('Takes effect immediately. Existing players keep their current name until they change it.');

        return self::SUCCESS;
    }
}
