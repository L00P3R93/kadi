<?php

namespace App\Livewire\Guest;

use App\Services\GamesService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Casino Games — Slots & Table Games | Kadi Kings')]
class GamesList extends Component
{
    public Collection $games;

    public bool $showComingSoonModal = false;

    public string $selectedGame = '';

    public function mount(): void
    {
        $this->games = app(GamesService::class)->all();
    }

    public function openComingSoon(string $gameName): void
    {
        $this->selectedGame = $gameName;
        $this->showComingSoonModal = true;
    }

    public function render()
    {
        return view('livewire.guest.games-list')
            ->layout('layouts.guest')
            ->layoutData([
                'description' => 'Browse all casino games at Kadi Kings — slots, blackjack, roulette, poker, and live dealer games available in Kenya.',
                'page'        => 'games',
            ]);
    }
}
