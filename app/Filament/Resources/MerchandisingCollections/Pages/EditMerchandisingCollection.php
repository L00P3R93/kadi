<?php

namespace App\Filament\Resources\MerchandisingCollections\Pages;

use App\Filament\Resources\MerchandisingCollections\MerchandisingCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMerchandisingCollection extends EditRecord
{
    protected static string $resource = MerchandisingCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
