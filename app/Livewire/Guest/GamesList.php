<?php

namespace App\Livewire\Guest;

use App\Services\GamesService;
use Illuminate\Support\Collection;
use Livewire\Component;

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
            ->layout('layouts.guest');
    }
}
