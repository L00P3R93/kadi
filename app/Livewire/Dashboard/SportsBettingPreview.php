<?php

namespace App\Livewire\Dashboard;

use App\Services\CachedSportsbookService;
use Carbon\Carbon;
use Livewire\Component;

class SportsBettingPreview extends Component
{
    public array $events = [];

    public string $sport = 'soccer_epl';

    public function mount(CachedSportsbookService $service): void
    {
        $sportsFlat = $service->getSportsFlat();
        $firstFootball = collect($sportsFlat)->firstWhere('display_group', 'Football');
        $this->sport = $firstFootball['key'] ?? 'soccer_epl';

        $this->events = collect($service->getEventsForSport($this->sport))
            ->filter(fn ($e) => Carbon::parse($e['commence_time'])->isFuture())
            ->take(6)
            ->values()
            ->toArray();
    }

    public function getEventOdds(string $eventId): array
    {
        $event = collect($this->events)->firstWhere('id', $eventId);

        if (! $event) {
            return [];
        }

        return $event['markets']['h2h'] ?? [];
    }

    public function render()
    {
        return view('livewire.dashboard.sports-betting-preview');
    }
}
