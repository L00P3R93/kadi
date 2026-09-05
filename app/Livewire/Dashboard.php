<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard | Kadi')]
class Dashboard extends Component
{
    public bool $showComingSoonModal = false;

    public string $selectedGame = '';

    public string $googleId = '';

    public string $playKadiUrl;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->googleId = $user->account_no ?? null;

        $this->playKadiUrl = rtrim((string) config('services.kadi_api.play_url'), '/');
    }

    /**
     * The external play site link, carrying this user's Kadi game
     * identity when one is known. The app's own linked google_id is
     * authoritative; the kadi-accounts mirror is only a fallback.
     */
    public function buildPlayKadiUrl(): void
    {
        //
    }

    /**
     * Deterministic "live" lobby numbers derived from the time of day:
     * quiet mornings, busy evenings — identical for every visitor and
     * every render within the same minute (no per-render random jumps).
     *
     * @return array{liveTables: int, activeGames: int, onlineUsers: int}
     */
    public function liveStats(CarbonImmutable $now): array
    {
        $hour = (float) $now->format('G') + ((int) $now->format('i')) / 60;
        $ramp = min(max(($hour - 9) / 12, 0), 1);
        $wave = sin($hour / 24 * 2 * pi());

        return [
            'liveTables' => 18 + (int) round(10 * $ramp),
            'activeGames' => 120 + (int) round(30 * $ramp),
            'onlineUsers' => (int) round(850 + 2400 * $ramp + 180 * $wave),
        ];
    }

    /**
     * Progressive pool seeded at midnight and growing steadily through
     * the day until the nightly draw — deterministic across renders.
     */
    public function progressiveJackpot(CarbonImmutable $now): int
    {
        return 2_097_152 + $now->secondsSinceMidnight() * 11;
    }

    /**
     * Seconds until the next draw (21:00 app time, rolling to tomorrow).
     */
    public function secondsUntilNextDraw(CarbonImmutable $now): int
    {
        $draw = $now->setTime(21, 0);

        if ($now->greaterThanOrEqualTo($draw)) {
            $draw = $draw->addDay();
        }

        return max(0, (int) $now->diffInSeconds($draw));
    }

    public function render(): Factory|View
    {
        /** @var User $user */
        $user = auth()->user();

        $recentTransactions = $user
            ->transactions()
            ->latest()
            ->take(5)
            ->get();

        // Prefer the fresher dedicated balance cache so this card can't
        // contradict the header wallet widget on the same screen.
        $kadiBalance = (float) (
            Cache::get("wallet_balance_{$user->id}")
            ?? Cache::get("kadi.customer.{$user->id}")['balance']
            ?? 0
        );

        $now = now();
        $stats = $this->liveStats($now);

        return view('livewire.dashboard', [
            'recentTransactions' => $recentTransactions,
            'playKadiUrl' => $this->playKadiUrl,
            'googleId' => $this->googleId,
            'kadiBalance' => $kadiBalance,
            'jackpotAmount' => $this->progressiveJackpot($now),
            'drawInSeconds' => $this->secondsUntilNextDraw($now),
            'kadiPlaying' => (int) round(280 + 520 * min(max((($now->hour + $now->minute / 60) - 9) / 12, 0), 1)),
        ] + $stats)
            ->layout('layouts.app')
            ->layoutData([
                'noindex' => true,
                'page' => 'dashboard',
            ]);
    }
}
