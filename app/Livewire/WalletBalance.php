<?php

namespace App\Livewire;

use App\Facades\KadiApi;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class WalletBalance extends Component
{
    public ?float $balance = null;

    public bool $hasError = false;

    public bool $needsLoad = false;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Try dedicated balance cache first
        $cached = Cache::get("wallet_balance_{$user->id}");
        if ($cached !== null) {
            $this->balance = (float) $cached;

            return;
        }

        // Fall back to the customer profile cache populated by HandleLogin
        $profile = Cache::get("kadi.customer.{$user->id}");
        if ($profile && array_key_exists('balance', $profile)) {
            $this->balance = (float) $profile['balance'];
            Cache::put("wallet_balance_{$user->id}", $this->balance, now()->addMinutes(5));

            return;
        }

        $this->needsLoad = true;
    }

    public function loadBalance(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Check dedicated balance cache
        $cached = Cache::get("wallet_balance_{$user->id}");
        if ($cached !== null) {
            $this->balance = (float) $cached;
            $this->needsLoad = false;

            return;
        }

        // Check the shared customer profile cache before hitting the API
        $profile = Cache::get("kadi.customer.{$user->id}");
        if ($profile && array_key_exists('balance', $profile)) {
            $this->balance = (float) $profile['balance'];
            Cache::put("wallet_balance_{$user->id}", $this->balance, now()->addMinutes(5));
            $this->needsLoad = false;

            return;
        }

        $this->hasError = false;
        $this->doFetch($user);
    }

    public function refresh(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->hasError = false;

        // Bust all caches so doFetch always hits the API fresh
        Cache::forget("wallet_balance_{$user->id}");
        Cache::forget("wallet_last_checked_{$user->id}");
        Cache::forget("kadi.customer.{$user->id}");

        $this->doFetch($user);
    }

    private function doFetch(User $user): void
    {
        // Step 1 — user must be linked to KadiApi
        if (! $user->linked_id) {
            Log::warning("WalletBalance: user {$user->id} has no linked_id");
            $this->hasError = true;
            $this->needsLoad = false;

            return;
        }

        // Step 2 — verify kadi DB record exists (non-blocking, cached 60 min)
        $this->checkKadiDbLinkage($user);

        // Step 3 — fetch balance from KadiApi
        try {
            $response = KadiApi::getCustomer($user->linked_id);
            $profile = $response['data'] ?? $response;
            $balance = (float) ($profile['balance'] ?? 0);

            // Refresh the shared customer cache (preserves google_id and all other fields)
            Cache::put("kadi.customer.{$user->id}", $profile, now()->addHour());

            // Balance-specific caches
            Cache::put("wallet_balance_{$user->id}", $balance, now()->addMinutes(5));
            Cache::put("wallet_last_checked_{$user->id}", now()->toISOString(), now()->addMinutes(5));

            $this->balance = $balance;
            $this->dispatch('wallet-refreshed');
        } catch (\Throwable $e) {
            Log::warning("WalletBalance: KadiApi fetch failed for user {$user->id}: ".$e->getMessage());
            $this->hasError = true;
        }

        $this->needsLoad = false;
    }

    private function checkKadiDbLinkage(User $user): void
    {
        $cacheKey = "wallet_linked_{$user->id}";

        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            $exists = DB::connection('kadi')
                ->table('accounts')
                ->where('email', $user->email)
                ->exists();

            Cache::put($cacheKey, $exists, now()->addMinutes(60));
        } catch (\Throwable $e) {
            // Non-blocking — log and continue to balance fetch
            Log::warning("WalletBalance: kadi DB check failed for user {$user->id}: ".$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.wallet-balance');
    }
}
