<?php

namespace App\Livewire;

use App\Facades\KadiApi;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Kadi — Kenya\'s Card Game')]
class Welcome extends Component
{
    public string $playKadiUrl;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            $this->playKadiUrl = route('login');

            return;
        }

        $cacheKey = "kadi.customer.{$user->id}";
        $profile = Cache::get($cacheKey);

        if ($profile === null) {
            $profile = $this->refreshProfile($user, $cacheKey);
        }

        $googleId = $user->account_no ?? null;

        $this->playKadiUrl = 'https://kadi-kings.co.ke'
            .($googleId ? '?ggid='.$googleId : '');
    }

    public function livePlayers(): int
    {
        return Cache::remember('kadi.live_players', now()->addMinutes(5), function () {
            try {
                $response = Http::get('https://gameapi.kadikings.co.ke/kadi/get_user_totals.php')
                    ->throw()
                    ->json();

                return ($response['jackpots']['total'] ?? 0)
                    + ($response['single']['total'] ?? 0)
                    + ($response['tournaments']['total'] ?? 0);
            } catch (ConnectionException|RequestException $e) {
                Log::error('Welcome: Failed to fetch live players: '.$e->getMessage());

                return 0;
            }
        });
    }

    private function refreshProfile(User $user, string $cacheKey): array
    {
        if (! $user->linked_id) {
            return [];
        }

        try {
            $response = KadiApi::getCustomer($user->linked_id);
            $profile = $response['data'] ?? $response;

            $googleId = $user->account_no ?? null;

            if ($googleId !== null) {
                $profile['google_id'] = $googleId;
            }

            Cache::put($cacheKey, $profile, now()->addHour());

            return $profile;
        } catch (RequestException|ConnectionException $e) {
            Log::error("Welcome: KadiApi fetch failed for user {$user->id}: ".$e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Welcome: Failed to refresh profile for user {$user->id}: ".$e->getMessage());
        }

        return [];
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.welcome', [
            'livePlayers' => $this->livePlayers(),
            'users' => User::count(),
        ])
            ->layout('layouts.guest')
            ->layoutData([
                'description' => 'Play Kadi — Kenya\'s own card game online. Free to join, competitive tables, deposits via M-Pesa.',
                'page' => 'home',
            ]);
    }
}
