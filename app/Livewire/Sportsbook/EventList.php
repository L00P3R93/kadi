<?php

namespace App\Livewire\Sportsbook;

use App\Services\CachedSportsbookService;
use App\Services\OddsApiService;
use Livewire\Attributes\On;
use Livewire\Component;

class EventList extends Component
{
    public string $sport = 'soccer_epl';

    public array $events = [];

    public array $odds = [];

    public array $oddsMap = [];

    public bool $showMarketsModal = false;

    public ?string $modalEventId = null;

    public array $modalEvent = [];

    public array $modalMarkets = [];

    public bool $modalLoading = false;

    public bool $modalLoadingMore = false;

    public string $modalActiveTab = 'all';

    public function mount(): void
    {
        $cachedService = app(CachedSportsbookService::class);
        $sportsFlat = $cachedService->getSportsFlat();
        $firstFootball = collect($sportsFlat)->firstWhere('display_group', 'Football');
        $this->sport = 'soccer_epl'; // default selected group otherwise change to $firstFootball['key'] ?? 'soccer_epl' to select the first key
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
        $this->events = $cachedService->getEventsForSport($this->sport);
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
        $this->showMarketsModal = true;
        $this->modalEventId = $eventId;
        $this->modalLoading = false;
        $this->modalLoadingMore = true;
        $this->modalMarkets = [];
        $this->modalActiveTab = 'all';
        $this->modalEvent = collect($this->events)->firstWhere('id', $eventId) ?? [];

        // Load all 3 cached markets immediately
        $cachedService = app(CachedSportsbookService::class);
        $this->modalMarkets = $cachedService->getAllMarketsForEvent($this->sport, $eventId);
    }

    public function loadMoreMarkets(): void
    {
        if (! $this->modalEventId) {
            $this->modalLoadingMore = false;

            return;
        }

        $service = app(OddsApiService::class);
        $aggregated = $service->getSportEventOdds($this->sport, $this->modalEventId);

        if (! empty($aggregated['markets'])) {
            // Merge: API data wins; cached markets fill any gaps
            $this->modalMarkets = array_merge($this->modalMarkets, $aggregated['markets']);
        }

        $this->modalLoadingMore = false;
    }

    public function closeMarketsModal(): void
    {
        $this->showMarketsModal = false;
        $this->modalEventId = null;
        $this->modalEvent = [];
        $this->modalMarkets = [];
        $this->modalLoadingMore = false;
    }

    public function setModalTab(string $tab): void
    {
        $this->modalActiveTab = $tab;
    }

    public function getEventOdds(string $eventId): array
    {
        $event = collect($this->events)->firstWhere('id', $eventId);
        if (! $event) {
            return [];
        }

        return $event['markets']['h2h'] ?? [];
    }

    public function getEventSpreads(string $eventId): array
    {
        $event = collect($this->events)->firstWhere('id', $eventId);
        if (! $event) {
            return [];
        }

        $spreads = $event['markets']['spreads']['outcomes'] ?? [];
        $title = $event['markets']['spreads']['title'] ?? 'Handicap';

        $byTeam = [];
        foreach ($spreads as $outcome) {
            $team = $outcome['name'];
            if (! isset($byTeam[$team])) {
                $byTeam[$team] = $outcome;
            }
        }

        return [
            'spreads' => array_values($byTeam),
            'title' => $title,
        ];
    }

    public function getEventTotals(string $eventId): array
    {
        $event = collect($this->events)->firstWhere('id', $eventId);
        if (! $event) {
            return [];
        }

        $title = $event['markets']['totals']['title'] ?? 'Over/Under';
        $totals = $event['markets']['totals']['outcomes'] ?? [];

        $byPoint = [];
        foreach ($totals as $outcome) {
            $point = $outcome['point'] ?? 0;
            $isOver = stripos($outcome['name'], 'over') !== false;
            $key = $point.($isOver ? '_over' : '_under');
            if (! isset($byPoint[$key])) {
                $byPoint[$key] = $outcome;
            }
        }

        usort($byPoint, function ($a, $b) {
            $aPoint = $a['point'] ?? 0;
            $bPoint = $b['point'] ?? 0;
            if ($aPoint !== $bPoint) {
                return $aPoint <=> $bPoint;
            }
            $aOver = stripos($a['name'], 'over') !== false ? 0 : 1;
            $bOver = stripos($b['name'], 'over') !== false ? 0 : 1;

            return $aOver <=> $bOver;
        });

        $result = [];
        if (count($byPoint) >= 2) {
            $result = [$byPoint[0], $byPoint[1]];
        } elseif (count($byPoint) === 1) {
            $result = [$byPoint[0]];
        }

        return [
            'results' => $result,
            'title' => $title,
        ];
    }

    public function render()
    {
        return view('livewire.sportsbook.event-list');
    }
}
