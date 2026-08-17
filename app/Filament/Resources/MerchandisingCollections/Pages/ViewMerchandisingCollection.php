<?php

namespace App\Filament\Resources\MerchandisingCollections\Pages;

use App\Filament\Resources\MerchandisingCollections\MerchandisingCollectionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMerchandisingCollection extends ViewRecord
{
    protected static string $resource = MerchandisingCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
