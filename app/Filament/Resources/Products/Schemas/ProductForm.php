<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('short_description'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('specifications'),
                Select::make('product_type')
                    ->options(ProductType::class)
                    ->default('reward')
                    ->required(),
                Select::make('status')
                    ->options(ProductStatus::class)
                    ->default('active')
                    ->required(),
                TextInput::make('money_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('coin_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('original_money_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('original_coin_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('currency')
                    ->required()
                    ->default('KES'),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reserved_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('low_stock_threshold')
                    ->required()
                    ->numeric()
                    ->default(5),
                Toggle::make('is_featured')
                    ->required(),
                Toggle::make('is_new')
                    ->required(),
                Toggle::make('is_popular')
                    ->required(),
                Toggle::make('is_trending')
                    ->required(),
                Toggle::make('is_promotional')
                    ->required(),
                TextInput::make('estimated_value')
                    ->numeric(),
                Toggle::make('requires_shipping')
                    ->required(),
                Toggle::make('is_redeemable_with_coins')
                    ->required(),
                Toggle::make('is_purchasable_with_money')
                    ->required(),
                TextInput::make('metadata'),
            ]);
    }
}
