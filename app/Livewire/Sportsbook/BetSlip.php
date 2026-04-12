<?php

namespace App\Livewire\Sportsbook;

use Livewire\Attributes\On;
use Livewire\Component;

class BetSlip extends Component
{
    public array $selections = [];

    public string $stake = '';

    #[On('alpine-bet-slip-updated')]
    public function onAlpineBetSlipUpdated(array $selections): void
    {
        $this->selections = $selections;
    }

    public function getBetType(): string
    {
        if (count($this->selections) === 1) {
            $firstSel = reset($this->selections);

            return $firstSel['marketLabel'] ?? 'Single Bet';
        }

        return 'Multi Bet';
    }

    public function getSelectionCount(): int
    {
        return count($this->selections);
    }

    public function getTotalOdds(): float
    {
        if (empty($this->selections)) {
            return 0.0;
        }

        return round(
            array_reduce(
                $this->selections,
                fn ($carry, $sel) => $carry * (float) ($sel['price'] ?? 1),
                1.0
            ),
            2
        );
    }

    public function getPossibleWin(): float
    {
        $stake = (float) $this->stake;
        if ($stake <= 0 || empty($this->selections)) {
            return 0.0;
        }

        return round($stake * $this->getTotalOdds(), 2);
    }

    public function setStake(int|string $amount): void
    {
        $this->stake = (string) $amount;
    }

    public function clearSlip(): void
    {
        $this->selections = [];
        $this->stake = '';
    }

    public function placeBet(): void
    {
        if (empty($this->selections)) {
            session()->flash('bet_error', 'Please add at least one selection.');

            return;
        }

        if (! is_numeric($this->stake) || (float) $this->stake <= 0) {
            session()->flash('bet_error', 'Please enter a valid stake amount.');

            return;
        }

        session()->flash('bet_success', 'Bet placed successfully! (Demo)');
        $this->clearSlip();
        $this->dispatch('alpine-bet-slip-updated', selections: []);
    }

    public function render()
    {
        return view('livewire.sportsbook.bet-slip');
    }
}
