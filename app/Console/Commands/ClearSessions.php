<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('session:clear
{--user= : Only clear sessions for a specific user ID}
{--all : Clear all sessions including online users}')]
#[Description('Delete active sessions from the database, forcing users to re-login.')]
class ClearSessions extends Command
{
    public function handle(): int
    {
        $userId = $this->option('user');
        $clearAll = $this->option('all');

        $query = DB::table('sessions');

        if ($userId) {
            $deleted = $query->where('user_id', $userId)->delete();
            $this->info("Cleared {$deleted} session(s) for user ID {$userId}.");

            return self::SUCCESS;
        }

        if ($clearAll) {
            $deleted = $query->delete();
            $this->info("Cleared all {$deleted} session(s).");

            return self::SUCCESS;
        }

        $deleted = $query->where('last_activity', '<', now()->subMinutes(
            (int) config('session.lifetime', 120)
        )->timestamp)->delete();

        $this->info("Cleared {$deleted} expired session(s).");
        $this->line('Use <comment>--all</comment> to force-clear all sessions, or <comment>--user={id}</comment> for a specific user.');

        return self::SUCCESS;
    }
}
