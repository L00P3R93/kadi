<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Kadi Rules — How to Play the Kadi Card Game | Kadi Online')]
class Rules extends Component
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

        // The app's own account_no is the player's game identity (kadi:sync-google-id copies it to
        // kadi.accounts.google_id), as on the home page and dashboard.
        // No KadiApi or game-database call before this page is sent.
        $googleId = $user->account_no ?: null;

        $this->playKadiUrl = config('services.kadi_api.play_url').($googleId ? '?ggid='.$googleId : '');
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.rules')
            ->layoutData([
                'description' => 'Kadi rules explained: how to play Kadi, deal the cards, chain combos, use Aces, Jokers and question cards, and win by calling Kadi. A complete guide for 2–4 players.',
                'page' => 'rules',
            ]);
    }
}
