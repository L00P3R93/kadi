<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                 * ============================================================
                 * PRODUCT OVERVIEW
                 * ============================================================
                 */

                Section::make('Product Overview')
                    ->description('Basic information and product classification.')
                    ->icon(Heroicon::OutlinedCube)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Product Name')
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('sku')
                            ->label('SKU')
                            ->weight('medium')
                            ->copyable()
                            ->copyMessage('SKU copied')
                            ->icon(Heroicon::OutlinedTag),

                        TextEntry::make('productCategory.name')
                            ->label('Category')
                            ->placeholder('Uncategorized')
                            ->icon(Heroicon::OutlinedFolder),

                        TextEntry::make('product_type')
                            ->label('Product Type')
                            ->badge()
                            ->formatStateUsing(
                                fn ($state) => $state instanceof ProductType
                                    ? str($state->value)->headline()
                                    : str($state)->headline()
                            ),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn ($state) => $state instanceof ProductStatus
                                    ? str($state->value)->headline()
                                    : str($state)->headline()
                            ),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable()
                            ->copyMessage('Slug copied')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                 * ============================================================
                 * PRICING
                 * ============================================================
                 */

                Section::make('Pricing')
                    ->description('Current and original product pricing.')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextEntry::make('money_price')
                            ->label('Money Price')
                            ->money(fn (Product $record) => $record->currency ?? 'KES')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),

                        TextEntry::make('coin_price')
                            ->label('Coin Price')
                            ->numeric()
                            ->suffix(' coins')
                            ->weight('bold')
                            ->size('lg')
                            ->color('warning'),

                        TextEntry::make('original_money_price')
                            ->label('Original Money Price')
                            ->money(fn (Product $record) => $record->currency ?? 'KES')
                            ->placeholder('-'),

                        TextEntry::make('original_coin_price')
                            ->label('Original Coin Price')
                            ->numeric()
                            ->suffix(' coins')
                            ->placeholder('-'),

                        TextEntry::make('currency')
                            ->label('Currency')
                            ->badge(),

                        TextEntry::make('estimated_value')
                            ->label('Estimated Value')
                            ->money(fn (Product $record) => $record->currency ?? 'KES')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),

                /*
                 * ============================================================
                 * INVENTORY
                 * ============================================================
                 */

                Section::make('Inventory')
                    ->description('Current stock and inventory thresholds.')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->schema([
                        TextEntry::make('stock_quantity')
                            ->label('Stock')
                            ->numeric()
                            ->weight('bold')
                            ->size('lg'),

                        TextEntry::make('reserved_quantity')
                            ->label('Reserved')
                            ->numeric(),

                        TextEntry::make('available_quantity')
                            ->label('Available')
                            ->state(
                                fn (Product $record) => max(
                                    0,
                                    (int) $record->stock_quantity
                                    - (int) $record->reserved_quantity
                                )
                            )
                            ->numeric()
                            ->weight('bold')
                            ->color(
                                fn (Product $record) => $record->stock_quantity
                                - $record->reserved_quantity
                                <= $record->low_stock_threshold
                                    ? 'warning'
                                    : 'success'
                            ),

                        TextEntry::make('low_stock_threshold')
                            ->label('Low Stock Threshold')
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 1]),

                /*
                 * ============================================================
                 * PRODUCT FEATURES
                 * ============================================================
                 */

                Section::make('Product Features')
                    ->description('Merchandising, purchasing and fulfillment settings.')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->schema([
                        IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean(),

                        IconEntry::make('is_new')
                            ->label('New')
                            ->boolean(),

                        IconEntry::make('is_popular')
                            ->label('Popular')
                            ->boolean(),

                        IconEntry::make('is_trending')
                            ->label('Trending')
                            ->boolean(),

                        IconEntry::make('is_promotional')
                            ->label('Promotional')
                            ->boolean(),

                        IconEntry::make('requires_shipping')
                            ->label('Requires Shipping')
                            ->boolean(),

                        IconEntry::make('is_redeemable_with_coins')
                            ->label('Coin Redemption')
                            ->boolean(),

                        IconEntry::make('is_purchasable_with_money')
                            ->label('Money Purchase')
                            ->boolean(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                 * ============================================================
                 * DESCRIPTION
                 * ============================================================
                 */

                Section::make('Product Description')
                    ->description('Customer-facing product information.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextEntry::make('short_description')
                            ->label('Short Description')
                            ->placeholder('No short description provided.')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->placeholder('No description provided.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                /*
                 * ============================================================
                 * SYSTEM INFORMATION
                 * ============================================================
                 */

                Section::make('System Information')
                    ->description('Internal record information.')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y, H:i')
                            ->since()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, H:i')
                            ->since()
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('-')
                            ->visible(
                                fn (Product $record): bool => $record->trashed()
                            ),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
