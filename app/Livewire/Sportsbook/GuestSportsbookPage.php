<?php

namespace App\Livewire\Sportsbook;

use Livewire\Component;

class GuestSportsbookPage extends Component
{
    public function render()
    {
        return view('livewire.sportsbook.guest-sportsbook-page')
            ->layout('layouts.guest');
    }
}
