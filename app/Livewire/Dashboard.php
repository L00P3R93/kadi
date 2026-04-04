<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $showComingSoonModal = false;
    public int $jackpotAmount = 2097152;

    public function refreshJackpot(): void
    {
        $this->jackpotAmount += rand(50, 100000);
    }

    public function pollJackpot(): void
    {
        $this->refreshJackpot();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        $recentTransactions = auth()->user()
            ->transactions()
            ->latest()
            ->take(5)
            ->get();

        $kadiCustomer = Cache::get('kadi.customer.'.auth()->id(), []);

        return view('livewire.dashboard', compact('recentTransactions') + [
            'liveTables'   => 24,
            'activeGames'  => 138,
            'onlineUsers'  => rand(1200, 4800),
            'kadiBalance'  => $kadiCustomer['balance'] ?? 0,
        ])
            ->layout('layouts.app');
    }
}
