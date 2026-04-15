<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Angel Palace — Kenya\'s Premier Online Casino')]
class Welcome extends Component
{
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
