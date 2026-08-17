<?php

namespace App\Filament\Marketing\Resources\AdCategories\Pages;

use App\Filament\Marketing\Resources\AdCategories\AdCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdCategories extends ListRecords
{
    protected static string $resource = AdCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
