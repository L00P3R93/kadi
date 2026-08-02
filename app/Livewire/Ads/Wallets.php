<?php

namespace App\Livewire\Ads;

use App\Models\AdProfile;
use App\Models\AdWallet;
use App\Models\AdWalletTransaction;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Ad Wallet | Kadi Kings')]
class Wallets extends Component
{
    use WithPagination;

    public AdWallet $wallet;

    // ── Transaction ledger ──────────────────────────────────────────
    public string $typeFilter = 'all';

    public int $perPage = 15;

    // ── Top-up modal ────────────────────────────────────────────────
    public bool $showTopUpModal = false;

    public bool $confirmStep = false;

    public bool $topUpSuccess = false;

    public string $amount = '';

    public string $phone_number = '';

    public function mount(): void
    {
        // NOTE: adjust this if you already resolve the current advertiser's
        // profile/wallet elsewhere (middleware, a User::adProfile() relation,
        // etc). I don't have App\Models\AdProfile, so this defensively
        // get-or-creates one + its wallet the first time a user visits this
        // page, mirroring how AdProfile/AdWallet are meant to be created
        // lazily per the schema doc.
        $user = auth()->user();
        $adProfile = AdProfile::query()->firstOrCreate([
            'user_id' => $user->id,
            'company_name' => $user->name,
            'company_phone' => $user->phone,
            'company_email' => $user->email,
        ]);

        $this->wallet = AdWallet::query()->firstOrCreate(
            ['ad_profile_id' => $adProfile->id],
            ['balance' => 0, 'currency' => 'KES']
        );

        $this->phone_number = (string) (auth()->user()->phone ?? '');
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    // ── Top-up flow ─────────────────────────────────────────────────

    protected function topUpRules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10', 'max:1000000'],
            'phone_number' => ['required', 'regex:/^(?:254|0)?7\d{8}$/'],
        ];
    }

    public function openTopUpModal(): void
    {
        $this->reset(['amount', 'confirmStep', 'topUpSuccess']);
        $this->phone_number = (string) (auth()->user()->phone ?? '');
        $this->resetErrorBag();
        $this->showTopUpModal = true;
    }

    /**
     * Step 1 → Step 2. Validates amount/phone before showing the
     * confirmation panel, so a bad amount never reaches "Confirm & Pay".
     */
    public function proceedToConfirm(): void
    {
        $this->validate($this->topUpRules());

        $this->confirmStep = true;
    }

    public function backToForm(): void
    {
        $this->confirmStep = false;
    }

    public function initiateTopUp(): void
    {
        $this->validate($this->topUpRules());

        $topUp = $this->wallet->adWalletTopUps()->create([
            'user_id' => auth()->id(),
            'amount' => $this->amount,
            'phone_number' => $this->phone_number,
            'transaction_ref' => (string) Str::uuid(),
            'status' => 'pending',
        ]);

        /*
        |------------------------------------------------------------
        | M-Pesa STK push
        |------------------------------------------------------------
        | Replace with your actual Daraja integration, e.g.:
        |
        |   app(MpesaService::class)->stkPush(
        |       phone: $topUp->phone_number,
        |       amount: $topUp->amount,
        |       reference: $topUp->transaction_ref,
        |   );
        |
        | This method only fires the *request* — crediting ad_wallets.balance,
        | flipping this AdWalletTopUp to status = 'completed' (+ completed_at),
        | and writing the matching AdWalletTransaction (type: 'topup') should
        | all happen in your STK callback/webhook handler, not here, since
        | that's the only place that knows the payment actually succeeded.
        */

        $this->confirmStep = false;
        $this->topUpSuccess = true;
    }

    public function closeTopUpModal(): void
    {
        $this->showTopUpModal = false;
        $this->reset(['amount', 'confirmStep', 'topUpSuccess']);
        $this->resetErrorBag();
    }

    // ── Live balance refresh while a top-up is pending ────────────────

    public function getHasPendingTopUpProperty(): bool
    {
        return $this->wallet->adWalletTopUps()
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->exists();
    }

    public function refreshWallet(): void
    {
        $this->wallet->refresh();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        $transactions = AdWalletTransaction::query()
            ->where('ad_wallet_id', $this->wallet->id)
            ->when($this->typeFilter !== 'all', fn ($query) => $query->where('type', $this->typeFilter))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.ads.wallets', [
            'transactions' => $transactions,
        ])->layout('layouts.app');
    }
}
