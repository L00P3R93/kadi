<?php

namespace App\Support;

use App\Jobs\ReportCustomerVerified;
use App\Models\User;

/**
 * The one place that decides when to tell KadiApi a player verified their e-mail and phone
 * (POST customers/{id}/verified). KadiApi then pays any referral bonus now due and grants the
 * signup bonus when the player qualifies. Reported for every player, referred or not.
 *
 * Called from both places that can complete the set, so the order does not matter:
 * ProcessVerifiedUser (e-mail verified and linked to KadiApi) and PhoneOtp (phone verified).
 *
 * KadiApi allows 30 calls a minute for the whole site: the job retries 429 with backoff.
 */
class CustomerVerification
{
    public static function isReady(User $user): bool
    {
        return $user->verified_reported_at === null
            && $user->linked_id !== null
            && $user->hasVerifiedEmail()
            && $user->hasVerifiedPhone();
    }

    public static function reportIfReady(User $user): void
    {
        if (self::isReady($user)) {
            ReportCustomerVerified::dispatch($user);
        }
    }
}
