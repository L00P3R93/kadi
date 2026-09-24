<?php

namespace App\Http\Middleware;

use App\Referrals\ReferralCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the code from a referral link (?ref=CODE, on any page) until the guest signs up: in the
 * session and in a 30-day cookie. Signed-in players are ignored (referrals are made at sign-up only).
 * It never redirects, never calls KadiApi and never logs the code.
 */
class CaptureReferralCode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('kadi.referrals.enabled') && $request->isMethod('GET') && $request->query->has('ref') && ! $request->user()) {
            $code = ReferralCode::normalize(is_string($request->query('ref')) ? $request->query('ref') : null);

            if (ReferralCode::isValidSignupCode($code)) {
                ReferralCode::remember($request, $code);
            }
        }

        return $next($request);
    }
}
