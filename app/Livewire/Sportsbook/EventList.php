<?php

namespace App\Livewire\Sportsbook;

use App\Services\OddsApiService;
use App\Support\SportsbookMarkets;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class EventList extends Component
{
    public string $sport = 'soccer_epl';

    public array $events = [];

    public array $odds = [];

    public array $oddsMap = [];

    public ?string $expandedEventId = null;

    public array $eventMarkets = [];

    public array $eventDetailOdds = [];

    public string $activeMarket = 'h2h';

    public bool $loadingMarkets = false;

    public function mount(OddsApiService $service): void
    {
        $this->events = $service->getEvents($this->sport);
        $this->odds = $service->getOdds($this->sport);
        $this->oddsMap = collect($this->odds)->keyBy('id')->toArray();
    }

    #[On('sport-selected')]
    public function onSportSelected(string $sport): void
    {
        $this->sport = $sport;
        $this->expandedEventId = null;
        $this->eventMarkets = [];
        $this->eventDetailOdds = [];

        $service = app(OddsApiService::class);
        $this->events = $service->getEvents($sport);
        $this->odds = $service->getOdds($sport);
        $this->oddsMap = collect($this->odds)->keyBy('id')->toArray();
    }

    public function refreshData(): void
    {
        Cache::forget("odds_api.events.{$this->sport}");
        Cache::forget("odds_api.odds.{$this->sport}");

        $service = app(OddsApiService::class);
        $this->events = $service->getEvents($this->sport);
        $this->odds = $service->getOdds($this->sport);
        $this->oddsMap = collect($this->odds)->keyBy('id')->toArray();
    }

    public function expandEvent(string $eventId): void
    {
        if ($this->expandedEventId === $eventId) {
            $this->expandedEventId = null;
            $this->eventMarkets = [];
            $this->eventDetailOdds = [];

            return;
        }

        $this->expandedEventId = $eventId;
        $this->activeMarket = 'h2h';
        $this->loadingMarkets = true;
        $this->eventMarkets = [];
        $this->eventDetailOdds = [];

        $service = app(OddsApiService::class);

        // 1 credit — cached 1 hour
        $markets = $service->getEventMarkets($this->sport, $eventId);
        $this->eventMarkets = SportsbookMarkets::sortMarkets($markets);

        // Pre-load h2h only (free from getH2HOddsOnly cache)
        $h2hOddsAll = $service->getH2HOddsOnly($this->sport);
        $eventH2h = collect($h2hOddsAll)->firstWhere('id', $eventId);
        if ($eventH2h) {
            foreach ($eventH2h['bookmakers'] ?? [] as $bm) {
                foreach ($bm['markets'] ?? [] as $market) {
                    if ($market['key'] === 'h2h') {
                        $this->eventDetailOdds['h2h'] = ['outcomes' => $market['outcomes'] ?? []];
                        break 2;
                    }
                }
            }
        }

        $this->loadingMarkets = false;
    }

    public function parseEventOddsResponse(array $oddsData): array
    {
        $markets = [];

        foreach ($oddsData['bookmakers'] ?? [] as $bm) {
            foreach ($bm['markets'] ?? [] as $market) {
                $key = $market['key'];
                if (str_ends_with($key, '_lay')) {
                    continue;
                }
                if (! isset($markets[$key])) {
                    $markets[$key] = ['outcomes' => $market['outcomes'] ?? []];
                }
            }
            break; // use only first bookmaker
        }

        return $markets;
    }

    public function selectMarket(string $marketKey): void
    {
        $this->activeMarket = $marketKey;

        // Already loaded
        if (isset($this->eventDetailOdds[$marketKey])) {
            return;
        }

        // h2h — check cached h2h odds
        if ($marketKey === 'h2h') {
            $service = app(OddsApiService::class);
            $h2hOddsAll = $service->getH2HOddsOnly($this->sport);
            $eventH2h = collect($h2hOddsAll)->firstWhere('id', $this->expandedEventId);
            if ($eventH2h) {
                foreach ($eventH2h['bookmakers'] ?? [] as $bm) {
                    foreach ($bm['markets'] ?? [] as $market) {
                        if ($market['key'] === 'h2h') {
                            $this->eventDetailOdds['h2h'] = ['outcomes' => $market['outcomes'] ?? []];

                            return;
                        }
                    }
                }
            }
        }

        // Fetch from live API for this specific market
        $this->loadingMarkets = true;
        $service = app(OddsApiService::class);
        $oddsData = $service->getEventOddsByMarkets($this->sport, $this->expandedEventId, [$marketKey]);
        $newMarkets = $this->parseEventOddsResponse($oddsData);
        $this->eventDetailOdds = array_merge($this->eventDetailOdds, $newMarkets);
        $this->loadingMarkets = false;
    }

    public function getOutcomesForActiveMarket(): array
    {
        return $this->eventDetailOdds[$this->activeMarket]['outcomes'] ?? [];
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

    public function getAllMarketsForEvent(string $eventId): array
    {
        $eventData = collect($this->events)->firstWhere('id', $eventId);

        if (! $eventData) {
            return [];
        }

        $bookmakers = $eventData['bookmakers'] ?? [];

        if (empty($bookmakers)) {
            return [];
        }

        $markets = [];

        foreach ($bookmakers[0]['markets'] ?? [] as $market) {
            $key = $market['key'];
            if (str_ends_with($key, '_lay')) {
                continue;
            }
            $markets[$key] = ['outcomes' => $market['outcomes'] ?? []];
        }

        return $markets;
    }

    public function render()
    {
        return view('livewire.sportsbook.event-list');
    }
}
