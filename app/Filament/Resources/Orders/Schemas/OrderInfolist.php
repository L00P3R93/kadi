<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('order_number'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('payment_state')
                    ->badge(),
                TextEntry::make('fulfillment_state')
                    ->badge(),
                TextEntry::make('payment_method')
                    ->badge(),
                TextEntry::make('currency'),
                TextEntry::make('subtotal_money')
                    ->numeric(),
                TextEntry::make('discount_money')
                    ->numeric(),
                TextEntry::make('shipping_money')
                    ->numeric(),
                TextEntry::make('tax_money')
                    ->numeric(),
                TextEntry::make('grand_total_money')
                    ->numeric(),
                TextEntry::make('subtotal_coins')
                    ->numeric(),
                TextEntry::make('discount_coins')
                    ->numeric(),
                TextEntry::make('grand_total_coins')
                    ->numeric(),
                IconEntry::make('requires_shipping')
                    ->boolean(),
                TextEntry::make('payment_due_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('paid_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('cancelled_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('cancellation_reason')
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
                    ->visible(fn (Order $record): bool => $record->trashed()),
            ]);
    }
}
