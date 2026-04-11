<?php

namespace App\Livewire\Sportsbook;

use App\Services\OddsApiService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class GuestEventList extends Component
{
    public string $sport = 'soccer_epl';

    public array $events = [];

    public array $odds = [];

    public array $oddsMap = [];

    public array $betSlip = [];

    public function mount(OddsApiService $service): void
    {
        $this->odds = $service->getOdds($this->sport);
        $this->events = $this->odds;
        $this->oddsMap = collect($this->odds)->keyBy('id')->toArray();
    }

    #[On('sport-selected')]
    public function onSportSelected(string $sport): void
    {
        $this->sport = $sport;
        $service = app(OddsApiService::class);
        $this->odds = $service->getOdds($sport);
        $this->events = $this->odds;
        $this->oddsMap = collect($this->odds)->keyBy('id')->toArray();
    }

    public function refreshData(): void
    {
        Cache::forget("odds_api.events.{$this->sport}");
        Cache::forget("odds_api.odds.{$this->sport}");
        $service = app(OddsApiService::class);
        $this->odds = $service->getOdds($this->sport);
        $this->events = $this->odds;
        $this->oddsMap = collect($this->odds)->keyBy('id')->toArray();
    }

    public function toggleBetSlip(string $eventId, string $team, float $price, string $homeTeam, string $awayTeam): void
    {
        if (isset($this->betSlip[$eventId])) {
            unset($this->betSlip[$eventId]);
        } else {
            $this->betSlip[$eventId] = [
                'team'  => $team,
                'price' => $price,
                'label' => "{$homeTeam} vs {$awayTeam}",
            ];
        }

        $this->dispatch('guest-bet-slip-updated', selections: $this->betSlip);
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
        return view('livewire.sportsbook.guest-event-list');
    }
}
