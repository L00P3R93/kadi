<?php

namespace App\Filament\Marketing\Resources\AdCategories\Pages;

use App\Filament\Marketing\Resources\AdCategories\AdCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdCategory extends CreateRecord
{
    protected static string $resource = AdCategoryResource::class;
}
