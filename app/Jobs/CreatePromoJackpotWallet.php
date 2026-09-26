<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Asks the game server to create the promo jackpot wallet for a player whose promo code earned the
 * signup bonus:
 *
 *     GET {game_api_url}/mpesa_create_jp_wallet.php?type=BRONZE&amount=20&id={linked_id}
 *
 * Dispatched only by ReportCustomerVerified, when POST customers/{id}/verified returned a
 * signup_bonus (KadiApi's answer that the code was still usable) for a player who signed up with a
 * promo code.
 *
 * The endpoint has no idempotency key, so a second call could create a second wallet. The row is
 * claimed (pending) before the call and the request is never retried: a failure or timeout is
 * recorded as failed / unknown for someone to check on the game server, like an SMS send.
 * Same nested limits as the other jobs: timeout 60s < worker --timeout 90s < retry_after 120s.
 */
class CreatePromoJackpotWallet implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const PENDING = 'pending';

    public const SENT = 'sent';

    /** The game server answered with an error: no wallet was created (as far as we can tell). */
    public const FAILED = 'failed';

    /** Timeout or connection error: the wallet may or may not exist. */
    public const UNKNOWN = 'unknown';

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(public User $user) {}

    public function uniqueId(): string
    {
        return (string) $this->user->id;
    }

    public function handle(): void
    {
        $this->user->refresh();

        if ($this->user->signup_promo_code === null || $this->user->linked_id === null) {
            return;
        }

        // Claim first, so a duplicate job (or a second verified report) can never call twice.
        $claimed = DB::table('users')
            ->where('id', $this->user->id)
            ->whereNull('promo_jackpot_wallet_status')
            ->update(['promo_jackpot_wallet_status' => self::PENDING, 'promo_jackpot_wallet_at' => now()]);

        if ($claimed === 0) {
            return;
        }

        $this->record($this->request());
    }

    private function request(): string
    {
        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->get(rtrim((string) config('services.kadi_api.game_api_url'), '/').'/mpesa_create_jp_wallet.php', [
                    'type' => config('kadi.promotions.jackpot_wallet.type'),
                    'amount' => config('kadi.promotions.jackpot_wallet.amount'),
                    'id' => $this->user->linked_id,
                ]);
        } catch (ConnectionException) {
            Log::warning("Promo jackpot wallet for user {$this->user->id}: no answer from the game server, check whether it was created");

            return self::UNKNOWN;
        } catch (\Throwable $e) {
            Log::error("Promo jackpot wallet for user {$this->user->id} failed: ".$e::class);

            return self::UNKNOWN;
        }

        if ($response->successful()) {
            Log::info("Promo jackpot wallet requested for user {$this->user->id}");

            return self::SENT;
        }

        Log::warning("Promo jackpot wallet for user {$this->user->id} refused: HTTP {$response->status()}");

        return $response->serverError() ? self::UNKNOWN : self::FAILED;
    }

    private function record(string $status): void
    {
        $this->user->forceFill(['promo_jackpot_wallet_status' => $status, 'promo_jackpot_wallet_at' => now()])->save();
    }
}
