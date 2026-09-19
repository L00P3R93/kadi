<?php

namespace App\Console\Commands;

use App\Support\NameGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('blocked-names:check {name : The name to test, in quotes if it has spaces}')]
#[Description('Say whether a name would be refused, and which entry matched (players are never told which).')]
class BlockedNameCheck extends Command
{
    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $term = NameGuard::matchingTerm($name);

        if ($term === null) {
            $this->info("\"{$name}\" is allowed.");

            return self::SUCCESS;
        }

        $this->error("\"{$name}\" is blocked by the entry \"{$term}\".");

        return self::FAILURE;
    }
}
