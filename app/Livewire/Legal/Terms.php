<?php

namespace App\Livewire\Legal;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Terms of Service | Kadi')]
class Terms extends Component
{
    public function render(): \Illuminate\Contracts\View\View|Factory|View
    {
        return view('livewire.legal.terms');
    }
}
