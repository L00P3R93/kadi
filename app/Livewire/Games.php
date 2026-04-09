<?php

namespace App\Livewire;

use App\Services\GamesService;
use Illuminate\Support\Collection;
use Livewire\Component;

class Games extends Component
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
        return view('livewire.games')
            ->layout('layouts.app');
    }
}
