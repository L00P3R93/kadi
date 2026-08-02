<?php

namespace App\Livewire\Tables;

use App\Models\AdPricingTier;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class PricingTiersTable extends Component
{
    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.tables.pricing-tiers-table', [
            'model' => AdPricingTier::class,
            'columns' => [
                'duration_seconds' => 'Duration (Seconds)',
                'base_cost' => 'Base Cost',
                'actions' => 'Actions',
            ],
            'searchable' => ['duration_seconds', 'base_cost'],
            'unsortable' => ['actions'],
            'customColumns' => [
                'base_cost' => 'components.table.amount',
                'actions' => 'components.table.pricing-tier-actions',
            ],
        ]);
    }
}
