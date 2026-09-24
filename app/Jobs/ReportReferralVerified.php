<?php

namespace App\Jobs;

use App\Facades\KadiApi;
use App\Models\User;
use App\Referrals\ReferralVerification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Tells KadiApi that a player verified their e-mail and phone, which pays their referrer the sign-up
 * bonus (and the first-deposit bonus if they already deposited). Safe to repeat: KadiApi pays each
 * milestone once, and `referred: false` (not a referral) is a normal answer, not an error.
 *
 * Dispatch it only through ReferralVerification::reportIfReady(). Same nested limits as the other
 * jobs: timeout 60s < worker --timeout 90s < retry_after 120s.
 */
class ReportReferralVerified implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /** @var list<int> */
    public array $backoff = [30, 120, 600, 1800];

    public int $timeout = 60;

    public function __construct(public User $user) {}

    public function uniqueId(): string
    {
        return (string) $this->user->id;
    }

    public function handle(): void
    {
        $this->user->refresh();

        if (! ReferralVerification::isReady($this->user)) {
            return;
        }

        try {
            KadiApi::reportReferralVerified((int) $this->user->linked_id);
        } catch (RequestException $e) {
            $status = $e->response->status();

            // 429 and 5xx: try again later. Anything else will not get better by retrying.
            if ($status === 429 || $status >= 500) {
                throw $e;
            }

            Log::warning("Referral verified report for user {$this->user->id} refused: HTTP {$status}");

            return;
        } catch (ConnectionException $e) {
            Log::warning("Referral verified report for user {$this->user->id}: connection error");

            throw $e;
        }

        $this->user->forceFill(['referral_verified_reported_at' => now()])->save();
    }
}
