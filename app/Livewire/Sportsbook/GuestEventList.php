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

    public bool $showMarketsModal = false;

    public ?string $modalEventId = null;

    public array $modalEvent = [];

    public array $modalMarkets = [];

    public bool $modalLoading = false;

    public bool $modalLoadingMore = false;

    public string $modalActiveTab = 'all';

    public function mount(CachedSportsbookService $cachedService): void
    {
        $this->events = $cachedService->getEventsForSport($this->sport);
        $this->oddsMap = collect($this->events)->keyBy('id')->toArray();
    }

    #[On('sport-selected')]
    public function onSportSelected(string $sport): void
    {
        $this->sport = $sport;
        $this->showMarketsModal = false;
        $this->modalEventId = null;
        $this->modalEvent = [];
        $this->modalMarkets = [];
        $this->modalLoading = false;
        $this->modalLoadingMore = false;
        $this->modalActiveTab = 'all';

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

    public function openMarketsModal(string $eventId): void
    {
        $this->showMarketsModal  = true;
        $this->modalEventId      = $eventId;
        $this->modalLoading      = false;
        $this->modalLoadingMore  = true;
        $this->modalMarkets      = [];
        $this->modalActiveTab    = 'all';
        $this->modalEvent        = collect($this->events)->firstWhere('id', $eventId) ?? [];

        // Show h2h immediately from JSON cache (no API call)
        $cachedService = app(CachedSportsbookService::class);
        $h2hOutcomes = $cachedService->getH2HOutcomesForEvent($this->sport, $eventId);

        // Fall back to bookmakers data in the event array
        if (empty($h2hOutcomes)) {
            foreach ($this->modalEvent['bookmakers'] ?? [] as $bm) {
                foreach ($bm['markets'] ?? [] as $market) {
                    if ($market['key'] === 'h2h') {
                        $h2hOutcomes = $market['outcomes'] ?? [];
                        break 2;
                    }
                }
            }
        }

        if (! empty($h2hOutcomes)) {
            $this->modalMarkets = ['h2h' => ['outcomes' => $h2hOutcomes]];
        }
    }

    public function loadMoreMarkets(): void
    {
        if (! $this->modalEventId) {
            $this->modalLoadingMore = false;

            return;
        }

        $service = app(OddsApiService::class);

        $marketKeys = $service->getEventMarkets($this->sport, $this->modalEventId);
        $marketKeys = SportsbookMarkets::sortMarkets($marketKeys);

        if (! empty($marketKeys)) {
            $limitedKeys = array_slice($marketKeys, 0, 10);
            $oddsData    = $service->getEventOddsByMarkets($this->sport, $this->modalEventId, $limitedKeys);
            $fetched     = $this->parseModalOdds($oddsData);
            // Merge: API data takes priority, keep h2h if API didn't return it
            $this->modalMarkets = array_merge($this->modalMarkets, $fetched);
        }

        $this->modalLoadingMore = false;
    }

    public function parseModalOdds(array $oddsData): array
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
            break; // first bookmaker only
        }

        return array_filter($markets, fn ($m) => ! empty($m['outcomes']));
    }

    public function closeMarketsModal(): void
    {
        $this->showMarketsModal = false;
        $this->modalEventId    = null;
        $this->modalEvent      = [];
        $this->modalMarkets    = [];
        $this->modalLoadingMore = false;
    }

    public function setModalTab(string $tab): void
    {
        $this->modalActiveTab = $tab;
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
