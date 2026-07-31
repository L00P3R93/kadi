<?php

namespace App\Livewire\Coins;

use App\Facades\KadiApi;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Buy Coins | Kadi Kings')]
class BuyCoins extends Component
{
    public array $kadiCustomer = [];

    public ?float $balance = null;

    /*
   |--------------------------------------------------------------------
   | Purchase options
   |--------------------------------------------------------------------
   | Kept identical to App\Livewire\Wallet\Index so the two pages never
   | drift out of sync. If you change pricing/packs, update both (or
   | extract this into a shared service/config if that becomes painful).
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
        // 'best' flags the strongest coins-per-shilling ratio in the set
        // (21 coins/KSH vs. 15-20 for the rest) — drives the "Best Value"
        // ribbon/spotlight in the UI.
        ['type' => 'coins', 'price' => 1000, 'coins' => 21000, 'best' => true],
    ];

    public ?int $selectedPurchaseIndex = null;

    public bool $processingPurchase = false;

    public ?string $purchaseError = null;

    public function mount(): void
    {
        $this->kadiCustomer = Cache::get('kadi.customer.'.auth()->id(), []);
        $this->balance = (float) ($this->kadiCustomer['balance'] ?? 0);
    }

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
     * Mirrors App\Livewire\Wallet\Index::requestStkPush.
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
                $this->balance = (float) $this->kadiCustomer['balance'];
                Cache::put("wallet_balance_{$user->id}", $this->balance, now()->addMinutes(5));
                $this->dispatch('wallet-refreshed');
            } catch (\Throwable $e) {
                Log::error("BuyCoins: Error fetching customer {$user->id} profile after requestStkPush");
            }
        }

        $this->processingPurchase = is_null($response);
        $this->purchaseError = $response ? null : 'Error processing purchase';
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.coins.⚡buy-coins')->layout('layouts.app');
    }
}
