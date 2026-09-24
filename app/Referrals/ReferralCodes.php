<?php

namespace App\Referrals;

use App\Facades\KadiApi;
use App\Models\User;
use App\Services\ReferralCodeResult;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * A player's own referral code, link and QR code. KadiApi stores them; this side generates them.
 *
 * Created lazily, the first time the player opens the referral page: at sign-up there is no KadiApi
 * customer yet (ProcessVerifiedUser links it later), most players never refer anyone, and existing
 * players get a code the same way without a backfill.
 */
class ReferralCodes
{
    /**
     * The player's code, link and QR code, creating them if they have none. Null when KadiApi could
     * not be reached or refused every attempt (the page says "not available right now").
     *
     * @return array{code: string, link: string, qr_code: string}|null
     */
    public function ensure(User $user): ?array
    {
        if (! $user->linked_id) {
            return null;
        }

        if (is_array($cached = Cache::get(self::cacheKey($user)))) {
            return $cached;
        }

        try {
            // Two tabs opening the page at once must not create two codes (the second PUT would win).
            return Cache::lock('referral-code-create:'.$user->id, 30)->block(15, fn () => $this->fetchOrCreate($user));
        } catch (LockTimeoutException) {
            return null;
        }
    }

    public static function cacheKey(User $user): string
    {
        return 'referral.code.'.$user->id;
    }

    /** @return array{code: string, link: string, qr_code: string}|null */
    private function fetchOrCreate(User $user): ?array
    {
        if (is_array($cached = Cache::get(self::cacheKey($user)))) {
            return $cached;
        }

        try {
            $existing = KadiApi::getReferralCode((int) $user->linked_id);
        } catch (\Throwable $e) {
            Log::warning("Referral code lookup for user {$user->id}: ".class_basename($e));

            return null;
        }

        if ($existing !== null && is_string($existing['code'] ?? null)) {
            return $this->remember($user, $existing['code'], $existing['link'] ?? null, $existing['qr_code'] ?? null);
        }

        for ($attempt = 1; $attempt <= (int) config('kadi.referrals.code_attempts', 5); $attempt++) {
            $code = ReferralCode::generate();
            $link = ReferralCode::link($code);
            $result = KadiApi::putReferralCode((int) $user->linked_id, $code, $link, ReferralQr::dataUri($link));

            if ($result->outcome === ReferralCodeResult::SAVED) {
                return $this->remember($user, (string) ($result->data['code'] ?? $code), $result->data['link'] ?? $link, $result->data['qr_code'] ?? null);
            }

            if ($result->outcome !== ReferralCodeResult::TAKEN) {
                return null;
            }
        }

        Log::error("Referral code for user {$user->id}: every generated code was taken.");

        return null;
    }

    /** @return array{code: string, link: string, qr_code: string} */
    private function remember(User $user, string $code, ?string $link, ?string $qrCode): array
    {
        $link = is_string($link) && $link !== '' ? $link : ReferralCode::link($code);

        $value = [
            'code' => $code,
            'link' => $link,
            // Only ever render our own data URIs; anything else stored in KadiApi is redrawn here.
            'qr_code' => is_string($qrCode) && str_starts_with($qrCode, 'data:image/') ? $qrCode : ReferralQr::dataUri($link),
        ];

        Cache::put(self::cacheKey($user), $value, now()->addDay());

        return $value;
    }
}
