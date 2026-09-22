<?php

namespace App\Http\Controllers\Api;

use App\Events\WalletBalanceUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalletWebhookRequest;
use App\Livewire\WalletBalance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * POST /api/v1/wallet-webhooks
 *
 * KadiApi pushes a signed event here whenever a customer's wallet balance changes, so this site
 * can show it within seconds instead of waiting on the 30s wire:poll. Every response is 200: a
 * bad signature or malformed body already got a 401/422 before reaching here, and everything
 * else (unmapped customer, stale event, duplicate delivery) is a legitimate no-op that KadiApi
 * must not retry.
 */
class WalletWebhookController extends Controller
{
    private const SEEN_TTL_HOURS = 24;

    private const VERSION_TTL_DAYS = 30;

    public function __invoke(WalletWebhookRequest $request): JsonResponse
    {
        $data = $request->validated();
        $eventId = $data['event_id'];
        $seenKey = "kadi-webhook:seen:{$eventId}";

        if (Cache::has($seenKey)) {
            return response()->json(['status' => 'ok']);
        }

        Cache::put($seenKey, true, now()->addHours(self::SEEN_TTL_HOURS));

        $users = User::query()->where('linked_id', $data['customer_id'])->get();

        if ($users->count() !== 1) {
            Log::warning('Wallet webhook: customer_id did not map to exactly one user', [
                'event_id' => $eventId,
                'customer_id' => $data['customer_id'],
                'matched' => $users->count(),
            ]);

            return response()->json(['status' => 'ok']);
        }

        /** @var User $user */
        $user = $users->first();

        $versionKey = "wallet_balance_version_{$user->id}";
        $lastVersion = Cache::get($versionKey);

        if ($lastVersion !== null && $data['balance_version'] <= $lastVersion) {
            return response()->json(['status' => 'ok']);
        }

        $this->applyBalance($user, (float) $data['balance'], (int) $data['balance_version']);

        return response()->json(['status' => 'ok']);
    }

    private function applyBalance(User $user, float $balance, int $balanceVersion): void
    {
        $profile = Cache::get("kadi.customer.{$user->id}", []);
        $profile['balance'] = $balance;
        Cache::put("kadi.customer.{$user->id}", $profile, now()->addHour());

        Cache::put("wallet_balance_{$user->id}", $balance, now()->addSeconds(WalletBalance::BALANCE_TTL_SECONDS));
        Cache::put("wallet_last_checked_{$user->id}", now()->toISOString(), now()->addSeconds(WalletBalance::BALANCE_TTL_SECONDS));
        Cache::put("wallet_balance_version_{$user->id}", $balanceVersion, now()->addDays(self::VERSION_TTL_DAYS));

        try {
            broadcast(new WalletBalanceUpdated($user->id, $balance));
        } catch (Throwable $e) {
            // A Reverb outage must never turn this endpoint's required 200 into a 500 — the cache
            // is already warm, so the open tab falls back to its next wire:poll tick.
            Log::warning('Wallet webhook: broadcast failed', ['user_id' => $user->id]);
        }
    }
}
