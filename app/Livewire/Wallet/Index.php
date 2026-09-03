<?php

namespace App\Livewire\Wallet;

use App\Facades\KadiApi;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Wallet | Kadi')]
class Index extends Component
{
    public string $filter = 'all';

    public array $kadiCustomer = [];

    public array $transactions = [];

    public bool $loadingTransactions = false;

    public bool $showDepositModal = false;

    public bool $showWithdrawModal = false;

    public bool $needsLoad = false;

    /*
     * Feature Toggles for Kadi Casino
     * Flipping any of these to true will restore Casino UI
     */
    public bool $depositWithdrawEnabled = true;

    public bool $coinsLogicEnabled = false;

    public bool $coinsWalletCardEnabled = false;

    public bool $withdrawalsTabEnabled = true;

    /*
    |--------------------------------------------------------------------
    | Wallet balance currency label
    |--------------------------------------------------------------------
    | Previously: session('currency.code', 'KES'). Set back to that
    | expression (or a computed property) to restore the KES label.
    */
    public string $walletCurrencyLabel = 'KES';

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

    public bool $processingWithdraw = false;

    public ?string $withdrawError = null;

    public string $depositAmount = '';

    public string $withdrawAmount = '';

    public bool $processingDeposit = false;

    public ?string $depositError = null;

    public bool $confirmingDeposit = false;

    public bool $confirmingWithdraw = false;

    public const MIN_DEPOSIT = 10;

    public const MIN_WITHDRAWAL = 100;

    public ?string $successMessage = null;

    public ?float $balance = null;

    public function mount(): void
    {
        $this->kadiCustomer = Cache::get('kadi.customer.'.auth()->id(), []);
        $this->balance = (float) ($this->kadiCustomer['balance'] ?? 0);

        // Cold cache (e.g. right after login): defer a live fetch via
        // wire:init instead of silently showing 0.
        if ($this->kadiCustomer === []) {
            $this->needsLoad = true;
        }

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

        $this->needsLoad = false;

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
            $this->balance = (float) ($data['balance'] ?? 0);
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
            $this->reloadCustomerProfile($user);
            $this->loadTransactions();
            $this->dispatch('wallet-refreshed');
        }
        $this->processingPurchase = is_null($response);
        $this->purchaseError = $response ? null : 'Error processing purchase';
    }

    /**
     * Re-fetch the authoritative customer profile from KadiApi and refresh
     * every balance cache. Shared by the deposit and withdraw success paths.
     */
    protected function reloadCustomerProfile(User $user): void
    {
        try {
            $response = KadiApi::getCustomer($user->linked_id);
            $profile = $response['data'] ?? $response;
            Cache::put('kadi.customer.'.$user->id, $profile, now()->addHour());
            $this->kadiCustomer = $profile;
            $this->balance = (float) ($profile['balance'] ?? 0);
        } catch (\Throwable $e) {
            Log::error("Error fetching customer {$user->id} profile after transaction");
        }
    }

    /**
     * Open the deposit modal in its entry state.
     */
    public function openDeposit(): void
    {
        $this->depositError = null;
        $this->confirmingDeposit = false;
        $this->showDepositModal = true;
    }

    /**
     * Open the withdraw modal in its entry state.
     */
    public function openWithdraw(): void
    {
        $this->withdrawError = null;
        $this->confirmingWithdraw = false;
        $this->showWithdrawModal = true;
    }

    /**
     * Step 1 of the deposit flow: validate the amount, then reveal the
     * confirmation step inside the modal. Nothing hits the API yet.
     */
    public function requestDeposit(): void
    {
        $this->depositError = null;
        $this->successMessage = null;

        if (($message = $this->guardLinkedAccount()) !== null) {
            $this->depositError = $message;

            return;
        }

        if ((float) $this->depositAmount < self::MIN_DEPOSIT) {
            $this->depositError = 'Minimum deposit amount is KES '.self::MIN_DEPOSIT.'.';

            return;
        }

        $this->confirmingDeposit = true;
    }

    public function cancelDeposit(): void
    {
        $this->confirmingDeposit = false;
    }

    /**
     * Step 2 of the deposit flow (after user confirmation): send the M-Pesa
     * STK push for the free-form amount. A successful response means the
     * prompt was SENT — the balance updates only after the user completes
     * payment on their phone, so no cache refresh or event dispatch here.
     */
    public function confirmDeposit(): void
    {
        $user = auth()->user();

        if (! $user?->linked_id) {
            return;
        }

        $this->processingDeposit = true;
        $success = KadiApi::stkDeposit($user, (int) round((float) $this->depositAmount));
        $this->processingDeposit = false;

        if (! $success) {
            $this->confirmingDeposit = false;
            $this->depositError = 'Deposit could not be initiated right now. Please try again shortly.';

            return;
        }

        $this->showDepositModal = false;
        $this->confirmingDeposit = false;
        $this->depositAmount = '';
        $this->successMessage = 'Deposit request sent. Confirm the M-Pesa prompt on your phone.';
    }

    /**
     * Step 1 of the withdraw flow: validate the amount, then reveal the
     * confirmation step inside the modal. The confirmation step is not
     * reachable below the minimum — the user sees an inline message
     * instead. Nothing hits the API yet.
     */
    public function requestWithdraw(): void
    {
        $this->withdrawError = null;
        $this->successMessage = null;

        if (($message = $this->guardLinkedAccount()) !== null) {
            $this->withdrawError = $message;

            return;
        }

        if (($message = $this->validateWithdrawAmount()) !== null) {
            $this->withdrawError = $message;

            return;
        }

        $this->confirmingWithdraw = true;
    }

    public function cancelWithdraw(): void
    {
        $this->confirmingWithdraw = false;
    }

    /**
     * Step 2 of the withdraw flow (after user confirmation).
     *
     * Success flow: close modal -> re-fetch authoritative profile into cache
     * -> dispatch('wallet-refreshed') so this page and all WalletBalance
     * widget instances resync -> loadTransactions() -> success banner.
     *
     * On failure the modal returns to the amount step with an inline
     * message; the balance is never optimistically decremented.
     *
     * TODO(Phase 3 sign-off): confirm the withdrawals/{encrypted_linked_id}
     * endpoint shape against staging KadiApi before enabling publicly.
     */
    public function confirmWithdraw(): void
    {
        $user = auth()->user();

        if (! $user?->linked_id) {
            return;
        }

        if (($message = $this->validateWithdrawAmount()) !== null) {
            $this->confirmingWithdraw = false;
            $this->withdrawError = $message;

            return;
        }

        $this->processingWithdraw = true;
        $success = KadiApi::withdraw($user, (float) $this->withdrawAmount);
        $this->processingWithdraw = false;

        if (! $success) {
            $this->confirmingWithdraw = false;
            // Do not optimistically decrement the balance.
            $this->withdrawError = 'Withdrawal could not be processed right now. Please try again shortly.';

            return;
        }

        $this->showWithdrawModal = false;
        $this->confirmingWithdraw = false;
        $this->withdrawAmount = '';
        $this->reloadCustomerProfile($user);
        $this->loadTransactions();
        $this->dispatch('wallet-refreshed');

        $this->successMessage = 'Withdrawal request received. You will receive an M-Pesa notification shortly.';
    }

    /**
     * Shared linked-account guard; returns an error message or null when ok.
     */
    protected function guardLinkedAccount(): ?string
    {
        if (! auth()->user()?->linked_id) {
            return 'Your account is not linked to a vault. Please contact support.';
        }

        return null;
    }

    /**
     * Withdraw-specific amount rules: minimum KES 100 and at most the
     * cached balance. Returns an error message or null when valid.
     */
    protected function validateWithdrawAmount(): ?string
    {
        $amount = (float) $this->withdrawAmount;

        if ($amount < self::MIN_WITHDRAWAL) {
            return 'Minimum withdrawal amount is KES '.self::MIN_WITHDRAWAL.'.';
        }

        if ($amount > (float) ($this->kadiCustomer['balance'] ?? 0)) {
            return 'Insufficient balance for this withdrawal.';
        }

        return null;
    }

    /**
     * Formatted M-Pesa number shown on confirmation steps.
     * Stored as 2547XXXXXXXX; displayed as +254 7XX XXX XXX.
     */
    public function getMpesaPhoneProperty(): ?string
    {
        $phone = auth()->user()->phone ?? null;

        if (! $phone) {
            return null;
        }

        if (strlen($phone) === 12 && str_starts_with($phone, '254')) {
            return '+254 '.substr($phone, 3, 3).' '.substr($phone, 6, 3).' '.substr($phone, 9);
        }

        return $phone;
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.wallet.index')->layout('layouts.app');
    }
}
