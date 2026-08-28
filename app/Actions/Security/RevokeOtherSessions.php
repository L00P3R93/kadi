<?php

namespace App\Actions\Security;

use Illuminate\Support\Facades\DB;

class RevokeOtherSessions
{
    /**
     * Sign the user out of every device except the current one.
     *
     * Only meaningful for the database session driver (sessions are
     * rows keyed by user_id); other drivers no-op gracefully.
     */
    public function __invoke($user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $query = DB::table('sessions')
            ->where('user_id', $user->getAuthIdentifier());

        $currentId = $this->currentSessionId();

        if ($currentId !== null) {
            $query->where('id', '!=', $currentId);
        }

        $query->delete();
    }

    /**
     * The current session id may be unavailable (e.g. no session on
     * the active request); null means "revoke everything".
     */
    private function currentSessionId(): ?string
    {
        $request = request();

        if (! $request->hasSession()) {
            return null;
        }

        return $request->session()->getId();
    }
}
