<?php

namespace App\Filament\Resources\RedemptionTransactions\Pages;

use App\Filament\Resources\RedemptionTransactions\RedemptionTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRedemptionTransactions extends ListRecords
{
    protected static string $resource = RedemptionTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
