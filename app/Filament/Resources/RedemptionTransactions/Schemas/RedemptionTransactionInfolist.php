<?php

namespace App\Filament\Resources\RedemptionTransactions\Schemas;

use App\Models\RedemptionTransaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RedemptionTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('order.id')
                    ->label('Order')
                    ->placeholder('-'),
                TextEntry::make('orderItem.id')
                    ->label('Order item')
                    ->placeholder('-'),
                TextEntry::make('source'),
                TextEntry::make('direction')
                    ->badge(),
                TextEntry::make('coin_amount')
                    ->numeric(),
                TextEntry::make('balance_before')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('balance_after')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('kadi_reference')
                    ->placeholder('-'),
                TextEntry::make('idempotency_key'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (RedemptionTransaction $record): bool => $record->trashed()),
            ]);
    }
}
