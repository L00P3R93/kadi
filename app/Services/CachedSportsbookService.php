<?php

namespace App\Services;

class CachedSportsbookService
{
    protected string $cachePath;

    public function __construct()
    {
        $this->cachePath = storage_path('app/sportsbook/cache.json');
    }

    public function exists(): bool
    {
        return file_exists($this->cachePath);
    }

    public function getAll(): array
    {
        if (! $this->exists()) {
            return [];
        }

        $data = json_decode(file_get_contents($this->cachePath), true);

        return $data ?? [];
    }

    public function getSports(): array
    {
        $data = $this->getAll();

        return collect($data['sports'] ?? [])
            ->map(fn ($s) => [
                'key' => $s['sport_key'],
                'title' => $s['sport_title'],
                'group' => $s['sport_group'],
            ])
            ->groupBy('group')
            ->toArray();
    }

    public function getEventsForSport(string $sportKey): array
    {
        $data = $this->getAll();
        $sport = collect($data['sports'] ?? [])->firstWhere('sport_key', $sportKey);

        return $sport['events'] ?? [];
    }

    public function getGeneratedAt(): ?string
    {
        $data = $this->getAll();

        return $data['generated_at'] ?? null;
    }

    public function getH2HOutcomesForEvent(string $sportKey, string $eventId): array
    {
        $events = $this->getEventsForSport($sportKey);
        $event = collect($events)->firstWhere('id', $eventId);

        if (! $event) {
            return [];
        }

        foreach ($event['bookmakers'] ?? [] as $bm) {
            foreach ($bm['markets'] ?? [] as $market) {
                if ($market['key'] === 'h2h') {
                    return $market['outcomes'] ?? [];
                }
            }
        }

        return [];
    }

    public function isExpired(): bool
    {
        $data = $this->getAll();

        if (empty($data['expires_at'])) {
            return true;
        }

        return now()->isAfter($data['expires_at']);
    }
}
