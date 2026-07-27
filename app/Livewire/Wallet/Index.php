<?php

namespace App\Livewire\Wallet;

use App\Facades\KadiApi;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public string $filter = 'all';

    public array $kadiCustomer = [];

    public array $transactions = [];

    public bool $loadingTransactions = false;

    public bool $showDepositModal = false;

    public bool $showWithdrawModal = false;

    /*
     * Feature Toggles for Kadi Casino
     * Flipping any of these to true will restore Casino UI
     */
    public bool $depositWithdrawEnabled = false;
    public bool $coinsWalletCardEnabled = false;
    public bool $withdrawalsTabEnabled = false;

    /*
    |--------------------------------------------------------------------
    | Wallet balance currency label
    |--------------------------------------------------------------------
    | Previously: session('currency.code', 'KES'). Set back to that
    | expression (or a computed property) to restore the KES label.
    */
    public string $walletCurrencyLabel = 'Coins';

    /*
   |--------------------------------------------------------------------
   | Load Wallet / Purchase options
   |--------------------------------------------------------------------
   */
    public array $purchaseOptions = [
        ['type' => 'emoji', 'label' => 'Emojis', 'price' => 49],
        ['type' => 'gift',  'label' => 'Gifts',  'price' => 99],
        ['type' => 'coins', 'price' => 10,   'coins' => 150],
        ['type' => 'coins', 'price' => 20,   'coins' => 320],
        ['type' => 'coins', 'price' => 50,   'coins' => 850],
        ['type' => 'coins', 'price' => 100,  'coins' => 1800],
        ['type' => 'coins', 'price' => 250,  'coins' => 4750],
        ['type' => 'coins', 'price' => 500,  'coins' => 10000],
        // 'best' flags this as the strongest coins-per-shilling ratio in the
        // set (21 coins/KSH vs. 15-20 for the rest) — drives the "Best Value"
        // ribbon in the UI. Move the flag if you add/change packages later.
        ['type' => 'coins', 'price' => 1000, 'coins' => 21000, 'best' => true],
    ];

    public ?int $selectedPurchaseIndex = null;
    public bool $processingPurchase = false;
    public ?string $purchaseError = null;

    public ?float $balance = null;

    public function mount(): void
    {
        $this->kadiCustomer = Cache::get('kadi.customer.'.auth()->id(), []);
        $this->balance = (float) $this->kadiCustomer['balance'] ?? 0;
        $this->loadTransactions();
    }

    #[On('wallet-refreshed')]
    public function syncCustomer(): void
    {
        $profile = Cache::get('kadi.customer.'.auth()->id());
        if ($profile) {
            $this->kadiCustomer = $profile;
            $this->balance = (float) $profile['balance'] ?? 0;
        }
    }

    public function loadTransactions(): void
    {
        $customerId = auth()->user()->linked_id;

        if (! $customerId) {
            return;
        }

        try {
            $response = KadiApi::getTransactions($customerId, $this->filter);
            $this->transactions = $response['transactions'] ?? [];
        } catch (\Throwable $e) {
            $this->transactions = [];
        }
    }

    public function setFilter(string $filter): void
    {
        // Guard: Don't allow filtering by a tab that's currently disabled
        if ($filter === 'withdrawals' && ! $this->withdrawalsTabEnabled) {
            return;
        }

        $this->filter = $filter;
        $this->loadTransactions();
    }

    public function refreshCustomer(): void
    {
        $customerId = auth()->user()->linked_id;

        if (! $customerId) {
            $this->kadiCustomer = [];

            return;
        }

        $cached = Cache::get('kadi.customer.'.auth()->id());

        if ($cached) {
            $this->kadiCustomer = $cached;
            $this->balance = (float) $cached['balance'] ?? 0;
            return;
        }

        try {
            $response = KadiApi::getCustomer($customerId);
            $data = $response['data'] ?? $response;
            Cache::put('kadi.customer.'.auth()->id(), $data, now()->addHour());
            $this->kadiCustomer = $data;
            $this->balance = (float) $data['balance'] ?? 0;
        } catch (\Throwable $e) {
            $this->kadiCustomer = [];
        }
    }

    /*
    |--------------------------------------------------------------------
    | Load Wallet / Purchases (M-Pesa STK Push)
    |--------------------------------------------------------------------
    | initiatePurchase() is wired to each tile in the UI. It currently
    | just marks the purchase as "processing" and calls the placeholder
    | below. Wire requestMpesaStkPush() up to the real external M-Pesa
    | API when it's ready.
    */
    public function initiatePurchase(int $index): void
    {
        $this->purchaseError = null;

        $option = $this->purchaseOptions[$index] ?? null;

        if (! $option) {
            $this->purchaseError = 'Invalid purchase option';
            return;
        }

        $phone = auth()->user()->phone ?? null;

        if (! $phone) {
            $this->purchaseError = 'Please add your phone number to complete this purchase';
            return;
        }

        $this->selectedPurchaseIndex = $index;
        $this->processingPurchase = true;
        $this->requestStkPush($option);
    }

    /**
     * Placeholder for the external M-Pesa STK push integration.
     *
     * Suggested implementation once the external API is available:
     *
     *   $reference = $option['type'] === 'coins'
     *       ? 'coins-'.$option['coins']
     *       : $option['type'].'-'.($option['label'] ?? '');
     *
     *   $response = KadiApi::stkPush([
     *       'phone'     => $phone,
     *       'amount'    => $option['price'],
     *       'reference' => $reference,
     *   ]);
     *
     *   // Handle response / dispatch an event so the UI can show
     *   // a "check your phone" prompt, then rely on a webhook/callback
     *   // to credit coins, emojis, or gifts once M-Pesa confirms payment.
     */
    protected function requestStkPush(array $option): void
    {
        $user = auth()->user();
        if ($option['type'] === 'coins') {
            $response = KadiApi::stkDeposit($user, $option['price']);
        } else {
            $response = KadiApi::stkLoad($user, $option);
        }
        if ($response) {
            try {
                $this->kadiCustomer = KadiApi::getCustomer($user->linked_id);
                Cache::put('kadi.customer.'.auth()->id(), $this->kadiCustomer, now()->addHour());
                $this->loadTransactions();
                $this->balance = (float) $this->kadiCustomer['balance'];
                Cache::put("wallet_balance_{$user->id}", $this->balance, now()->addMinutes(5));
            } catch (\Throwable $e) {
                Log::error("Error fetching customer {$user->id} profile after requestStkPush");
            }
        }
        $this->processingPurchase = is_null($response);
        $this->purchaseError = $response ? null : 'Error processing purchase';
    }


    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.wallet.index')->layout('layouts.app');
    }
}
