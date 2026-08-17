<?php

namespace App\Filament\Resources\Refunds\Schemas;

use App\Models\Refund;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RefundInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('payment.id')
                    ->label('Payment')
                    ->placeholder('-'),
                TextEntry::make('order.id')
                    ->label('Order'),
                TextEntry::make('amount_money')
                    ->numeric(),
                TextEntry::make('amount_coins')
                    ->numeric(),
                TextEntry::make('reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('reference')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('initiated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Refund $record): bool => $record->trashed()),
            ]);
    }
}
