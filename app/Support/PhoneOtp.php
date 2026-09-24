<?php

namespace App\Support;

use App\Models\User;
use App\Referrals\ReferralVerification;
use App\Services\TextSmsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * SMS codes that prove a player owns their phone number (the M-Pesa number deposits are charged to
 * and payouts are sent to).
 *
 * The code is kept only as an HMAC in the cache, bound to the player and the number it was sent to,
 * for `kadi.phone_otp.ttl_minutes`. A few wrong tries burn it. Sends cost money, so they are capped
 * per player, per number and per IP (SMS pumping). Codes are never logged.
 */
class PhoneOtp
{
    public const SENT = 'sent';

    public const COOLDOWN = 'cooldown';

    public const LIMITED = 'limited';

    public const FAILED = 'failed';

    public const VERIFIED = 'verified';

    public const INVALID = 'invalid';

    public const EXPIRED = 'expired';

    public const TOO_MANY = 'too_many';

    public function __construct(private TextSmsService $sms) {}

    public static function message(string $code): string
    {
        return "Kadi code: {$code}. Valid ".config('kadi.phone_otp.ttl_minutes').' min. Never share it.';
    }

    /**
     * Send a new code to the player's stored phone. Returns one of SENT, COOLDOWN, LIMITED, FAILED.
     */
    public function send(User $user, ?string $ip = null): string
    {
        $phone = $user->phone;

        if (! PhoneNumber::isValid($phone)) {
            return self::FAILED;
        }

        if ($this->secondsUntilResend($user) > 0) {
            return self::COOLDOWN;
        }

        $limits = [
            'phone-otp:user:'.$user->id => (int) config('kadi.phone_otp.sends_per_hour_user'),
            'phone-otp:phone:'.$phone => (int) config('kadi.phone_otp.sends_per_hour_phone'),
        ];

        if ($ip !== null) {
            $limits['phone-otp:ip:'.$ip] = (int) config('kadi.phone_otp.sends_per_hour_ip');
        }

        foreach ($limits as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return self::LIMITED;
            }
        }

        $code = str_pad((string) random_int(0, 10 ** config('kadi.phone_otp.length') - 1), config('kadi.phone_otp.length'), '0', STR_PAD_LEFT);

        // Counted before sending: a failed or slow send still costs a slot, so it cannot be hammered.
        foreach (array_keys($limits) as $key) {
            RateLimiter::hit($key, 3600);
        }
        RateLimiter::hit($this->resendKey($user), (int) config('kadi.phone_otp.resend_seconds'));

        if (! $this->sms->send($phone, self::message($code))) {
            return self::FAILED;
        }

        Cache::put($this->codeKey($user), [
            'hash' => $this->hash($user, $phone, $code),
            'phone' => $phone,
            'attempts' => 0,
        ], now()->addMinutes((int) config('kadi.phone_otp.ttl_minutes')));

        return self::SENT;
    }

    /**
     * Check a code. Returns one of VERIFIED, INVALID, EXPIRED, TOO_MANY.
     */
    public function verify(User $user, string $code): string
    {
        $key = $this->codeKey($user);
        $pending = Cache::get($key);

        // Expired, never sent, or sent to a number the player has since changed.
        if (! is_array($pending) || ($pending['phone'] ?? null) !== $user->phone) {
            return self::EXPIRED;
        }

        $code = preg_replace('/\D+/', '', $code) ?? '';

        if (! hash_equals($pending['hash'], $this->hash($user, $user->phone, $code))) {
            $pending['attempts']++;

            if ($pending['attempts'] >= (int) config('kadi.phone_otp.max_attempts')) {
                Cache::forget($key);

                return self::TOO_MANY;
            }

            Cache::put($key, $pending, now()->addMinutes((int) config('kadi.phone_otp.ttl_minutes')));

            return self::INVALID;
        }

        Cache::forget($key);
        $user->forceFill(['phone_verified_at' => now()])->save();

        ReferralVerification::reportIfReady($user);

        return self::VERIFIED;
    }

    public function secondsUntilResend(User $user): int
    {
        return RateLimiter::tooManyAttempts($this->resendKey($user), 1)
            ? RateLimiter::availableIn($this->resendKey($user))
            : 0;
    }

    public function hasPendingCode(User $user): bool
    {
        $pending = Cache::get($this->codeKey($user));

        return is_array($pending) && ($pending['phone'] ?? null) === $user->phone;
    }

    /** Drop any code in flight (e.g. the player changed their number). */
    public function forget(User $user): void
    {
        Cache::forget($this->codeKey($user));
    }

    private function hash(User $user, string $phone, string $code): string
    {
        return hash_hmac('sha256', "{$user->id}|{$phone}|{$code}", (string) config('app.key'));
    }

    private function codeKey(User $user): string
    {
        return 'phone-otp:code:'.$user->id;
    }

    private function resendKey(User $user): string
    {
        return 'phone-otp:resend:'.$user->id;
    }
}
