<?php

namespace App\Referrals;

use App\Jobs\ReportReferralVerified;
use App\Models\User;

/**
 * The one place that decides when to tell KadiApi a referred player is verified
 * (POST customers/{id}/referral/verified), which pays their referrer.
 *
 * Called from both places that can complete the set, so the order does not matter:
 * ProcessVerifiedUser (e-mail verified and linked to KadiApi) and PhoneOtp (phone verified).
 *
 * Only players who signed up with a code are reported: KadiApi allows 30 calls a minute for the
 * whole site, and a player without a code cannot be anyone's referral.
 */
class ReferralVerification
{
    public static function isReady(User $user): bool
    {
        return (bool) config('kadi.referrals.enabled')
            && $user->signup_referral_code !== null
            && $user->referral_verified_reported_at === null
            && $user->linked_id !== null
            && $user->hasVerifiedEmail()
            && $user->hasVerifiedPhone();
    }

    public static function reportIfReady(User $user): void
    {
        if (self::isReady($user)) {
            ReportReferralVerified::dispatch($user);
        }
    }
}
