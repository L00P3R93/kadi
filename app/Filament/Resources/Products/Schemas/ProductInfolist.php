<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_category_id')
                    ->numeric(),
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('short_description')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('product_type')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('money_price')
                    ->money(),
                TextEntry::make('coin_price')
                    ->money(),
                TextEntry::make('original_money_price')
                    ->money(),
                TextEntry::make('original_coin_price')
                    ->money(),
                TextEntry::make('currency'),
                TextEntry::make('stock_quantity')
                    ->numeric(),
                TextEntry::make('reserved_quantity')
                    ->numeric(),
                TextEntry::make('low_stock_threshold')
                    ->numeric(),
                IconEntry::make('is_featured')
                    ->boolean(),
                IconEntry::make('is_new')
                    ->boolean(),
                IconEntry::make('is_popular')
                    ->boolean(),
                IconEntry::make('is_trending')
                    ->boolean(),
                IconEntry::make('is_promotional')
                    ->boolean(),
                TextEntry::make('estimated_value')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('requires_shipping')
                    ->boolean(),
                IconEntry::make('is_redeemable_with_coins')
                    ->boolean(),
                IconEntry::make('is_purchasable_with_money')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Product $record): bool => $record->trashed()),
            ]);
    }
}
