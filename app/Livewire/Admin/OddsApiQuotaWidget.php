<?php

namespace App\Livewire\Admin;

use App\Models\OddsApiQuota;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Poll;
use Livewire\Component;

#[Poll(30000)]
class OddsApiQuotaWidget extends Component
{
    public int $remaining = 0;

    public int $used = 0;

    public mixed $updatedAt = null;

    public int $plan = 500;

    public function mount(): void
    {
        $this->load();
    }

    public function refresh(): void
    {
        Cache::forget('odds_api.quota');
        Cache::forget('odds_api.sports');
        $this->load();
    }

    public function percentUsed(): float
    {
        return round(($this->used / max($this->plan, 1)) * 100, 1);
    }

    public function isLow(): bool
    {
        return $this->remaining < 100;
    }

    public function render()
    {
        return view('livewire.admin.odds-api-quota-widget');
    }

    private function load(): void
    {
        $quota = Cache::get('odds_api.quota');

        if ($quota) {
            $this->remaining = (int) ($quota['remaining'] ?? 0);
            $this->used = (int) ($quota['used'] ?? 0);
            $this->updatedAt = now();
            return;
        }

        $model = OddsApiQuota::find(1);

        if ($model) {
            $this->remaining = $model->remaining;
            $this->used = $model->used;
            $this->updatedAt = $model->updated_at;
        }
    }
}
