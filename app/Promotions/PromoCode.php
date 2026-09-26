<?php

namespace App\Promotions;

use App\Facades\KadiApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Signup-bonus promo codes: the format, the code a guest arrived with (?promo=) and the lookup on
 * the sign-up screen. KadiApi decides whether a code is usable; an unknown code never blocks sign-up.
 *
 * Separate from referral codes (App\Referrals\ReferralCode): a player can send both.
 */
class PromoCode
{
    /** KadiApi's own rule for creating codes: 4-30 letters, digits, - or _, stored upper case. */
    public const FORMAT = '/^[A-Z0-9_-]{4,30}$/';

    public const SESSION_KEY = 'promo.code';

    public static function enabled(): bool
    {
        return (bool) config('kadi.promotions.enabled');
    }

    public static function normalize(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? null : $code;
    }

    public static function isValidFormat(?string $code): bool
    {
        $code = self::normalize($code);

        return $code !== null && preg_match(self::FORMAT, $code) === 1;
    }

    /**
     * Rules for the optional sign-up field. Normalise the input first (normalize()).
     *
     * @return array<int, string>
     */
    public static function signupRules(): array
    {
        return ['nullable', 'string', 'regex:'.self::FORMAT];
    }

    /** @return array<string, string> */
    public static function signupMessages(): array
    {
        return ['promo_code.regex' => __('A promo code is 4 to 30 letters or digits (- and _ are allowed).')];
    }

    /**
     * Remember a ?promo code for a guest: in the session and in a 30-day cookie (encrypted by
     * EncryptCookies), because the session does not last 30 days. The last code wins.
     */
    public static function remember(Request $request, string $code): void
    {
        $request->session()->put(self::SESSION_KEY, $code);

        Cookie::queue(Cookie::make(
            config('kadi.promotions.cookie'),
            $code,
            (int) config('kadi.promotions.cookie_days') * 24 * 60,
            secure: $request->isSecure() ?: null,
            httpOnly: true,
            sameSite: 'lax',
        ));
    }

    /** The code this guest arrived with, if any (session first, then the cookie). */
    public static function pending(Request $request): ?string
    {
        $code = self::normalize($request->session()->get(self::SESSION_KEY) ?? $request->cookie(config('kadi.promotions.cookie')));

        return self::isValidFormat($code) ? $code : null;
    }

    /** Signed up: the code is now on the user row, so drop the session copy and the cookie. */
    public static function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
        Cookie::queue(Cookie::forget(config('kadi.promotions.cookie')));
    }

    /**
     * Whether a player could sign up with this code right now.
     *
     * Returns ['valid' => true, 'code' => ..., 'expires_at' => ...], ['valid' => false], or null when
     * we could not tell (KadiApi unreachable, or this IP looked up too many codes): say nothing then.
     * Cached briefly, misses included; a code can be used up, so not for long.
     *
     * @return array{valid: bool, code?: string, expires_at?: ?string}|null
     */
    public static function lookup(string $code, string $ip): ?array
    {
        $code = self::normalize($code);

        if (! self::isValidFormat($code)) {
            return ['valid' => false];
        }

        $cacheKey = 'promo.lookup.'.sha1($code);

        if (($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        $limiterKey = 'promo-lookup:'.$ip;

        if (RateLimiter::tooManyAttempts($limiterKey, (int) config('kadi.promotions.lookups_per_minute', 10))) {
            return null;
        }

        RateLimiter::hit($limiterKey, 60);

        $result = KadiApi::lookupPromoCode($code);

        if ($result !== null) {
            Cache::put($cacheKey, $result, now()->addSeconds((int) config('kadi.promotions.lookup_cache_seconds', 60)));
        }

        return $result;
    }
}
