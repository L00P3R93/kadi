<?php

namespace App\Filament\Resources\ShippingAddresses\Pages;

use App\Filament\Resources\ShippingAddresses\ShippingAddressResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShippingAddress extends EditRecord
{
    protected static string $resource = ShippingAddressResource::class;

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
