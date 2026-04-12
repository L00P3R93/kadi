<?php

namespace App\Livewire\Sportsbook;

use App\Services\CachedSportsbookService;
use App\Services\OddsApiService;
use App\Support\SportsbookMarkets;
use Livewire\Attributes\On;
use Livewire\Component;

class GuestEventList extends Component
{
    public string $sport = 'soccer_epl';

    public array $events = [];

    public array $oddsMap = [];

    public ?string $expandedEventId = null;

    public string $activeMarket = 'h2h';

    public array $eventMarkets = [];

    public array $eventDetailOdds = [];

    public bool $loadingMarkets = false;

    public function mount(CachedSportsbookService $cachedService): void
    {
        $this->events = $cachedService->getEventsForSport($this->sport);
        $this->oddsMap = collect($this->events)->keyBy('id')->toArray();
    }

    #[On('sport-selected')]
    public function onSportSelected(string $sport): void
    {
        $this->sport = $sport;
        $this->expandedEventId = null;
        $this->eventMarkets = [];
        $this->eventDetailOdds = [];

        $cachedService = app(CachedSportsbookService::class);
        $this->events = $cachedService->getEventsForSport($sport);
        $this->oddsMap = collect($this->events)->keyBy('id')->toArray();
    }

    public function refreshData(): void
    {
        $cachedService = app(CachedSportsbookService::class);
        $this->events = $cachedService->getEventsForSport($this->sport);
        $this->oddsMap = collect($this->events)->keyBy('id')->toArray();
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

        // Step 1: Get available market keys — live API call (1 credit, cached 1hr)
        $service = app(OddsApiService::class);
        $this->eventMarkets = $service->getEventMarkets($this->sport, $eventId);
        $this->eventMarkets = SportsbookMarkets::sortMarkets($this->eventMarkets);

        // Step 2: Pre-load h2h odds from the JSON cache (free, instant)
        $cachedService = app(CachedSportsbookService::class);
        $h2hOutcomes = $cachedService->getH2HOutcomesForEvent($this->sport, $eventId);
        if (! empty($h2hOutcomes)) {
            $this->eventDetailOdds['h2h'] = ['outcomes' => $h2hOutcomes];
        }

        $this->loadingMarkets = false;
    }

    public function selectMarket(string $marketKey): void
    {
        $this->activeMarket = $marketKey;

        // Already loaded
        if (isset($this->eventDetailOdds[$marketKey])) {
            return;
        }

        // h2h — check JSON cache first
        if ($marketKey === 'h2h') {
            $cachedService = app(CachedSportsbookService::class);
            $h2hOutcomes = $cachedService->getH2HOutcomesForEvent($this->sport, $this->expandedEventId);
            if (! empty($h2hOutcomes)) {
                $this->eventDetailOdds['h2h'] = ['outcomes' => $h2hOutcomes];

                return;
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

    private function parseEventOddsResponse(array $oddsData): array
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
            break;
        }

        return $markets;
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

    public function render()
    {
        return view('livewire.sportsbook.guest-event-list');
    }
}
