<?php

namespace App\Jobs;

use App\Facades\KadiApi;
use App\Models\User;
use App\Notifications\PushMessageNotification;
use App\Notifications\SignupBonusGranted;
use App\Support\CustomerVerification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Tells KadiApi that a player verified their e-mail and phone (POST customers/{id}/verified). KadiApi
 * pays any referral bonus now due and grants the signup bonus when the player qualifies. Safe to
 * repeat: nothing is paid twice, and `signup_bonus: null` (no bonus) is a normal answer.
 *
 * Dispatch it only through CustomerVerification::reportIfReady(). Same nested limits as the other
 * jobs: timeout 60s < worker --timeout 90s < retry_after 120s.
 */
class ReportCustomerVerified implements ShouldBeUnique, ShouldQueue
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

        if (! CustomerVerification::isReady($this->user)) {
            return;
        }

        try {
            $response = KadiApi::reportVerified((int) $this->user->linked_id);
        } catch (RequestException $e) {
            $status = $e->response->status();

            // 429 and 5xx: try again later. Anything else will not get better by retrying.
            if ($status === 429 || $status >= 500) {
                throw $e;
            }

            Log::warning("Verified report for user {$this->user->id} refused: HTTP {$status}");

            return;
        } catch (ConnectionException $e) {
            Log::warning("Verified report for user {$this->user->id}: connection error");

            throw $e;
        }

        $this->user->forceFill(['verified_reported_at' => now()])->save();

        $bonus = $response['data']['signup_bonus'] ?? null;

        if (is_array($bonus)) {
            $this->announceSignupBonus($bonus);

            // A signup_bonus means KadiApi found the promo code still usable at this call. Its own
            // job, so a game-server failure never re-sends /verified (and the other way round).
            if ($this->user->signup_promo_code !== null) {
                CreatePromoJackpotWallet::dispatch($this->user);
            }
        }
    }

    /**
     * The balance just went up: drop the cached balance and promotions so the wallet shows it, and
     * tell the player. Only the bonus amount is shown, never the balance.
     */
    private function announceSignupBonus(array $bonus): void
    {
        $id = $this->user->id;
        Cache::forget("kadi.promotions.{$id}");
        Cache::forget("wallet_balance_{$id}");
        Cache::forget("kadi.customer.{$id}");

        $amount = number_format((float) ($bonus['net_amount'] ?? config('kadi.promotions.signup_bonus_amount')));

        $this->user->notify(new SignupBonusGranted($amount));
        $this->user->notify(new PushMessageNotification(
            'Signup bonus added',
            "KES {$amount} is in your vault. Play it to unlock withdrawals.",
            '/wallet',
            tag: 'signup-bonus',
        ));
    }
}
