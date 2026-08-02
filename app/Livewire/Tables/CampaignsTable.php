<?php

namespace App\Livewire\Tables;

use App\Models\AdCampaign;
use Livewire\Component;

class CampaignsTable extends Component
{
    public function render()
    {
        return view('livewire.tables.campaigns-table', [
            'model' => AdCampaign::class,
            'scope' => 'userCampaign',
            'columns' => [
                'adProfile.company_name' => 'Company',
                'name' => 'Name',
                'adCategory.name' => 'Category',
                'total_budget' => 'Budget',
                /*'priority' => 'Priority',
                'starts_at' => 'Starts',
                'ends_at' => 'Ends',*/
                'status' => 'Status',
                'actions' => 'Actions',
            ],
            'searchable' => ['adProfile.company_name', 'name', 'adCategory.name'],
            'unsortable' => ['actions'],
            'customColumns' => [
                'adCategory.name' => 'components.table.badge',
                'total_budget' => 'components.table.amount',
                // 'priority' => 'components.table.campaign-priority',
                'status' => 'components.table.campaign-status',
                'actions' => 'components.table.campaign-actions',
            ]
        ]);
    }
}
