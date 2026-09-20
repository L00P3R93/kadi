<?php

namespace App\Listeners;

use App\Models\User;
use App\Support\PlayerName;
use Illuminate\Auth\Events\Login;

class FlagNameUpdateRequired
{
    /**
     * Runs synchronously so the flag lands in the session that just logged in.
     */
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if (app()->bound('session.store') && PlayerName::needsUpdate($user)) {
            session()->put(PlayerName::SESSION_KEY, true);
        }
    }
}
