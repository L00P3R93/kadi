<?php

namespace App\Filament\Marketing\Resources\AdPricingTiers\Pages;

use App\Filament\Marketing\Resources\AdPricingTiers\AdPricingTierResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAdPricingTier extends EditRecord
{
    protected static string $resource = AdPricingTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
