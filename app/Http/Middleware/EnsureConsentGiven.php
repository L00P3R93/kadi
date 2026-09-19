<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds a freshly signed-in user on the consent page until they confirm they
 * are 18+ and accept the current terms. The requirement is flagged in the
 * session at login by FlagConsentRequired, so existing sessions are not
 * interrupted — only the user's next login.
 */
class EnsureConsentGiven
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $request->session()->get('consent.required')) {
            return $next($request);
        }

        if ($user->hasCurrentConsent()) {
            $request->session()->forget('consent.required');

            return $next($request);
        }

        if ($request->routeIs('consent.*', 'legal.*', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $request->isMethodSafe()) {
            abort(403, 'Please confirm your age and accept the terms to continue.');
        }

        return redirect()->guest(route('consent.show'));
    }
}
