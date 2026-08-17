<?php

namespace App\Filament\Marketing\Resources\AdPricingTiers\Pages;

use App\Filament\Marketing\Resources\AdPricingTiers\AdPricingTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdPricingTiers extends ListRecords
{
    protected static string $resource = AdPricingTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
