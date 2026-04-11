<?php

namespace App\Livewire\Sportsbook;

use Livewire\Attributes\On;
use Livewire\Component;

class GuestBetSlip extends Component
{
    public array $selections = [];

    public string $stake = '';

    #[On('guest-bet-slip-updated')]
    public function onBetSlipUpdated(array $selections): void
    {
        $this->selections = $selections;
    }

    public function removeSelection(string $eventId): void
    {
        unset($this->selections[$eventId]);
        $this->dispatch('guest-bet-slip-updated', selections: $this->selections);
    }

    public function clearSlip(): void
    {
        $this->selections = [];
        $this->stake = '';
    }

    public function potentialPayout(): float
    {
        if (! is_numeric($this->stake) || (float) $this->stake <= 0 || empty($this->selections)) {
            return 0.00;
        }

        return round((float) $this->stake * array_product(array_column($this->selections, 'price')), 2);
    }

    public function render()
    {
        return view('livewire.sportsbook.guest-bet-slip');
    }
}
