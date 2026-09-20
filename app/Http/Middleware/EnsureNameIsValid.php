<?php

namespace App\Http\Middleware;

use App\Support\PlayerName;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds a freshly signed-in player on the "choose a new name" page while their name breaks the
 * naming rules. The requirement is flagged in the session at login by FlagNameUpdateRequired, so
 * sessions already open are not interrupted, only the player's next login.
 */
class EnsureNameIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $request->session()->get(PlayerName::SESSION_KEY)) {
            return $next($request);
        }

        if (! PlayerName::needsUpdate($user)) {
            $request->session()->forget(PlayerName::SESSION_KEY);

            return $next($request);
        }

        if ($request->routeIs('name.*', 'consent.*', 'legal.*', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $request->isMethodSafe()) {
            abort(403, 'Please choose a new player name to continue.');
        }

        return redirect()->guest(route('name.edit'));
    }
}
