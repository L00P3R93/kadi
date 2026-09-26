<?php

namespace App\Http\Middleware;

use App\Promotions\PromoCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the code from a promo link (?promo=CODE, on any page) until the guest signs up: in the
 * session and in a 30-day cookie. Signed-in players are ignored (a code can only be attached at
 * sign-up). It never redirects, never calls KadiApi and never logs the code.
 */
class CapturePromoCode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (PromoCode::enabled() && $request->isMethod('GET') && $request->query->has('promo') && ! $request->user()) {
            $code = PromoCode::normalize(is_string($request->query('promo')) ? $request->query('promo') : null);

            if (PromoCode::isValidFormat($code)) {
                PromoCode::remember($request, $code);
            }
        }

        return $next($request);
    }
}
