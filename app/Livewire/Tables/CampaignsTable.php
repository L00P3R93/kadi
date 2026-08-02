<?php

namespace App\Livewire\Tables;

use App\Models\AdCampaign;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class CampaignsTable extends Component
{
    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.tables.campaigns-table', [
            'model' => AdCampaign::class,
            'scope' => 'userCampaign',
            'columns' => [
                'name' => 'Campaign',
                'ad_category_id' => 'Category',
                'status' => 'Status',
                'total_budget' => 'Budget',
                'escrowed_budget' => 'Remaining',
                'priority' => 'Priority',
                'actions' => 'Actions',
            ],
            'searchable' => ['name'],
            'unsortable' => ['ad_category_id', 'actions'],
            'customColumns' => [
                'ad_category_id' => 'components.table.campaign-category',
                'status' => 'components.table.campaign-status-badge',
                'total_budget' => 'components.table.campaign-money',
                'escrowed_budget' => 'components.table.campaign-money',
                'actions' => 'components.table.campaign-actions',
            ],
        ]);
    }
}
