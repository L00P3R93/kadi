<?php

namespace App\Referrals;

use App\Facades\KadiApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

/**
 * Referral code formats and the code a guest arrived with (?ref=).
 *
 * Two formats on purpose:
 * - a code typed or linked at sign-up may be a player's referral code OR an agent code (KadiApi
 *   decides), so it is accepted loosely: 2-32 letters, digits, `-` or `_`, stored upper case;
 * - a player's own code, generated here: 8 characters without the look-alikes 0 O 1 I L.
 */
class ReferralCode
{
    /** A code accepted on the sign-up form / in ?ref= (referral or agent code). */
    public const SIGNUP_FORMAT = '/^[A-Z0-9_-]{2,32}$/';

    /** No 0, O, 1, I or L: codes are read aloud and typed from posters. */
    public const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    public const SESSION_KEY = 'referral.code';

    public static function normalize(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? null : $code;
    }

    public static function isValidSignupCode(?string $code): bool
    {
        $code = self::normalize($code);

        return $code !== null && preg_match(self::SIGNUP_FORMAT, $code) === 1;
    }

    /**
     * Rules for the optional sign-up field. Normalise the input first (normalize()).
     *
     * @return array<int, string>
     */
    public static function signupRules(): array
    {
        return ['nullable', 'string', 'regex:'.self::SIGNUP_FORMAT];
    }

    /** @return array<string, string> */
    public static function signupMessages(): array
    {
        return ['referral_code.regex' => __('A code is 2 to 32 letters or digits (- and _ are allowed).')];
    }

    public static function generate(?int $length = null): string
    {
        $length ??= (int) config('kadi.referrals.code_length', 8);
        $max = strlen(self::ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }

    /** The link a player shares: the sign-up page with their code. */
    public static function link(string $code): string
    {
        return route('register', ['ref' => $code]);
    }

    /**
     * Remember a ?ref code for a guest: in the session and in a 30-day cookie (encrypted by
     * EncryptCookies), because the session does not last 30 days. The last code wins.
     */
    public static function remember(Request $request, string $code): void
    {
        $request->session()->put(self::SESSION_KEY, $code);

        Cookie::queue(Cookie::make(
            config('kadi.referrals.cookie'),
            $code,
            (int) config('kadi.referrals.cookie_days') * 24 * 60,
            secure: $request->isSecure() ?: null,
            httpOnly: true,
            sameSite: 'lax',
        ));
    }

    /** The code this guest arrived with, if any (session first, then the cookie). */
    public static function pending(Request $request): ?string
    {
        $code = self::normalize($request->session()->get(self::SESSION_KEY) ?? $request->cookie(config('kadi.referrals.cookie')));

        return self::isValidSignupCode($code) ? $code : null;
    }

    /**
     * First name of the player who owns a referral code, for "Invited by Jane" on sign-up. Null for
     * an unknown code (agent codes included) or when KadiApi cannot be reached; never blocks sign-up.
     * Cached, misses included, so a busy invite link costs one lookup per 10 minutes.
     */
    public static function inviterFirstName(string $code): ?string
    {
        $name = Cache::remember(
            'referral.lookup.'.sha1($code),
            now()->addMinutes((int) config('kadi.referrals.lookup_cache_minutes', 10)),
            fn () => (string) (KadiApi::lookupReferralCode($code)['name'] ?? ''),
        );

        $first = trim(strtok(trim((string) $name), ' ') ?: '');

        return $first === '' ? null : mb_substr($first, 0, 30);
    }

    /** Signed up: the code is now on the user row, so drop the session copy and the cookie. */
    public static function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
        Cookie::queue(Cookie::forget(config('kadi.referrals.cookie')));
    }
}
