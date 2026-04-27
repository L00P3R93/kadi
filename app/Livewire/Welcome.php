<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Angel Palace — Kenya\'s Premier Online Casino')]
class Welcome extends Component
{
    public string $playKadiUrl;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->playKadiUrl = route('login');
            return;
        }

        $profile  = Cache::get("kadi.customer.{$user->id}", []);
        $googleId = $profile['google_id'] ?? null;

        if (! $googleId) {
            $googleId = DB::connection('kadi')
                ->table('accounts')
                ->where('email', $user->email)
                ->value('google_id');

            if ($googleId) {
                $profile['google_id'] = $googleId;
                Cache::put("kadi.customer.{$user->id}", $profile, now()->addHour());
            }
        }

        $this->playKadiUrl = 'https://play.kadikings.co.ke'
            . ($googleId ? '?ggid=' . $googleId : '');
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
