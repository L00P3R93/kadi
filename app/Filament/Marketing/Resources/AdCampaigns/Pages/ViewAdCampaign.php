<?php

namespace App\Filament\Marketing\Resources\AdCampaigns\Pages;

use App\Filament\Marketing\Resources\AdCampaigns\Actions\ApproveCampaignAction;
use App\Filament\Marketing\Resources\AdCampaigns\Actions\CompleteCampaignAction;
use App\Filament\Marketing\Resources\AdCampaigns\Actions\PauseCampaignAction;
use App\Filament\Marketing\Resources\AdCampaigns\Actions\RejectCampaignAction;
use App\Filament\Marketing\Resources\AdCampaigns\Actions\SubmitCampaignReviewAction;
use App\Filament\Marketing\Resources\AdCampaigns\AdCampaignResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdCampaign extends ViewRecord
{
    protected static string $resource = AdCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SubmitCampaignReviewAction::make('submit_review'),
            ApproveCampaignAction::make('approve_campaign'),
            RejectCampaignAction::make('reject_campaign'),
            PauseCampaignAction::make('pause_campaign'),
            CompleteCampaignAction::make('complete_campaign'),
            EditAction::make(),
        ];
    }
}
