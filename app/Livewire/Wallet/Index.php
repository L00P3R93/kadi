<?php

namespace App\Livewire\Wallet;

use App\Facades\KadiApi;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public string $filter = 'all';

    public array $kadiCustomer = [];

    public array $transactions = [];

    public bool $loadingTransactions = false;

    public bool $showDepositModal = false;

    public bool $showWithdrawModal = false;

    public function mount(): void
    {
        $this->kadiCustomer = Cache::get('kadi.customer.'.auth()->id(), []);
        $this->loadTransactions();
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

            return;
        }

        try {
            $response = KadiApi::getCustomer($customerId);
            $data = $response['data'] ?? $response;
            Cache::put('kadi.customer.'.auth()->id(), $data, now()->addHour());
            $this->kadiCustomer = $data;
        } catch (\Throwable $e) {
            $this->kadiCustomer = [];
        }
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.wallet.index')->layout('layouts.app');
    }
}
