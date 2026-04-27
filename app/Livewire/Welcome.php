<?php

namespace App\Livewire;

use App\Facades\KadiApi;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Angel Palace — Kenya\'s Premier Online Casino')]
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
        $profile  = Cache::get($cacheKey);

        if ($profile === null) {
            $profile = $this->refreshProfile($user, $cacheKey);
        }

        $googleId = $profile['google_id'] ?? null;

        $this->playKadiUrl = 'https://play.kadikings.co.ke'
            . ($googleId ? '?ggid=' . $googleId : '');
    }

    private function refreshProfile(User $user, string $cacheKey): array
    {
        if (! $user->linked_id) {
            return [];
        }

        try {
            $response = KadiApi::getCustomer($user->linked_id);
            $profile  = $response['data'] ?? $response;

            $googleId = DB::connection('kadi')
                ->table('accounts')
                ->where('email', $user->email)
                ->value('google_id');

            if ($googleId !== null) {
                $profile['google_id'] = $googleId;
            }

            Cache::put($cacheKey, $profile, now()->addHour());

            return $profile;
        } catch (RequestException|ConnectionException $e) {
            Log::error("Welcome: KadiApi fetch failed for user {$user->id}: " . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error("Welcome: Failed to refresh profile for user {$user->id}: " . $e->getMessage());
        }

        return [];
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.welcome')
            ->layout('layouts.guest')
            ->layoutData([
                'description' => 'Experience world-class casino games at Angel Palace. Slots, live tables & more. Play now in Kenya.',
                'page'        => 'home',
            ]);
    }
}
