<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class FlagConsentRequired
{
    /**
     * Runs synchronously so the flag lands in the session that just logged in.
     */
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if (! $user->hasCurrentConsent() && app()->bound('session.store')) {
            session()->put('consent.required', true);
        }
    }
}
