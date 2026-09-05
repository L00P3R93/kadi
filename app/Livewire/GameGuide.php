<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Game Guide — How to Win | Kadi')]
class GameGuide extends Component
{
    public function render(): Factory|View
    {
        return view('livewire.game-guide')
            ->layoutData([
                'description' => 'Discover all Kadi game modes — single matches, tournaments, and jackpots. See stakes, multipliers, and prize pools.',
                'page' => 'game-guide',
            ]);
    }
}
