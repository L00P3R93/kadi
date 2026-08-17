<?php

namespace App\Filament\Resources\MerchandisingCollections\Pages;

use App\Filament\Resources\MerchandisingCollections\MerchandisingCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMerchandisingCollections extends ListRecords
{
    protected static string $resource = MerchandisingCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
