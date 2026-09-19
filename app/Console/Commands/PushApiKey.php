<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('push-api:key')]
#[Description('Generate a bearer key for the push API and the hash to put in .env (the key itself is shown once and never stored).')]
class PushApiKey extends Command
{
    public function handle(): int
    {
        $key = 'kpk_'.bin2hex(random_bytes(32));
        $hash = hash('sha256', $key);

        $this->newLine();
        $this->warn('Copy the key now. It is shown once and Kadi never stores it.');
        $this->newLine();
        $this->line('  Give the game server (as its Authorization bearer token):');
        $this->line("  <info>{$key}</info>");
        $this->newLine();
        $this->line('  Put only the HASH in this app\'s .env:');
        $this->line("  <info>PUSH_API_KEY_HASHES={$hash}</info>");
        $this->newLine();
        $this->line('  Rotating? Keep both hashes, comma separated, until the game server has switched:');
        $this->line('  <comment>PUSH_API_KEY_HASHES=<old-hash>,<new-hash></comment>');
        $this->newLine();
        $this->line('  Then run <comment>php artisan config:clear</comment> (production: the deploy\'s optimize).');
        $this->newLine();

        return self::SUCCESS;
    }
}
