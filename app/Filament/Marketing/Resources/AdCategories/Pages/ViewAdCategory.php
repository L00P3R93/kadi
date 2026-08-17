<?php

namespace App\Filament\Marketing\Resources\AdCategories\Pages;

use App\Filament\Marketing\Resources\AdCategories\AdCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdCategory extends ViewRecord
{
    protected static string $resource = AdCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
