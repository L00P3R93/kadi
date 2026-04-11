<?php

namespace App\Livewire\Dashboard;

use App\Services\OddsApiService;
use Carbon\Carbon;
use Livewire\Component;

class SportsBettingPreview extends Component
{
    public array $events = [];

    public array $oddsMap = [];

    public function mount(OddsApiService $service): void
    {
        $allEvents = $service->getEvents('soccer_epl');
        $allOdds   = $service->getOdds('soccer_epl');

        $this->events = collect($allEvents)
            ->filter(fn ($e) => Carbon::parse($e['commence_time'])->isFuture())
            ->take(6)
            ->values()
            ->toArray();

        $this->oddsMap = collect($allOdds)->keyBy('id')->toArray();
    }

    public function getEventOdds(string $eventId): array
    {
        $event = $this->oddsMap[$eventId] ?? null;

        if (! $event) {
            return [];
        }

        foreach ($event['bookmakers'] ?? [] as $bookmaker) {
            foreach ($bookmaker['markets'] ?? [] as $market) {
                if ($market['key'] === 'h2h') {
                    return $market['outcomes'] ?? [];
                }
            }
        }

        return [];
    }

    public function render()
    {
        return view('livewire.dashboard.sports-betting-preview');
    }
}
