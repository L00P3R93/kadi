<?php

namespace App\Livewire\Sportsbook;

use App\Services\CachedSportsbookService;
use Livewire\Component;

class SportsSidebar extends Component
{
    public array $sports = [];

    public string $selectedSport = 'soccer_epl';

    public function mount(CachedSportsbookService $service): void
    {
        $this->sports = $service->getSports();
    }

    public function selectSport(string $sportKey): void
    {
        $this->selectedSport = $sportKey;
        $this->dispatch('sport-selected', sport: $sportKey);
    }

    public function getSelectedGroup(): string
    {
        foreach ($this->sports as $group => $items) {
            foreach ($items as $sport) {
                if ($sport['key'] === $this->selectedSport) {
                    return $group;
                }
            }
        }

        return '';
    }

    public function render()
    {
        return view('livewire.sportsbook.sports-sidebar');
    }
}
