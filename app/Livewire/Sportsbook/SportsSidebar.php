<?php

namespace App\Livewire\Sportsbook;

use App\Services\OddsApiService;
use Livewire\Component;

class SportsSidebar extends Component
{
    public array $sports = [];

    public string $selectedSport = 'soccer_epl';

    public function mount(OddsApiService $service): void
    {
        $this->sports = collect($service->getSports())
            ->groupBy('group')
            ->toArray();
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
