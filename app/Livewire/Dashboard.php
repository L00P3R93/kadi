<?php

namespace App\Livewire;

use App\Services\GamesService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard | Angel Palace')]
class Dashboard extends Component
{
    public bool $showComingSoonModal = false;

    public string $selectedGame = '';

    public int $jackpotAmount = 2097152;

    public function refreshJackpot(): void
    {
        $this->jackpotAmount += rand(50, 100000);
    }

    public function pollJackpot(): void
    {
        $this->refreshJackpot();
    }

    public function openComingSoon(string $gameName): void
    {
        $this->selectedGame = $gameName;
        $this->showComingSoonModal = true;
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
            'liveTables' => 24,
            'activeGames' => 138,
            'onlineUsers' => rand(1200, 4800),
            'kadiBalance' => $kadiCustomer['balance'] ?? 0,
            'games' => app(GamesService::class)->all(),
        ])
            ->layout('layouts.app')
            ->layoutData([
                'noindex' => true,
                'page'    => 'dashboard',
            ]);
    }
}
