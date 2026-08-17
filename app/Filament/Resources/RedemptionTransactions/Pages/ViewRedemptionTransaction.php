<?php

namespace App\Filament\Resources\RedemptionTransactions\Pages;

use App\Filament\Resources\RedemptionTransactions\RedemptionTransactionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRedemptionTransaction extends ViewRecord
{
    protected static string $resource = RedemptionTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
