<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->sku),

                TextColumn::make('productCategory.name')
                    ->label('Category')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('product_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->sortable(),

                TextColumn::make('money_price')
                    ->label('Price')
                    ->money(fn ($record) => $record->currency ?? 'KES')
                    ->sortable()
                    ->description(
                        fn ($record) => $record->coin_price
                            ? number_format($record->coin_price).' coins'
                            : null
                    ),

                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($record) => match (true) {
                        $record->stock_quantity <= 0 => 'danger',
                        $record->stock_quantity <= $record->low_stock_threshold => 'warning',
                        default => 'success',
                    })
                    ->description(fn ($record) => $record->reserved_quantity > 0
                        ? "{$record->reserved_quantity} reserved"
                        : null
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->headline())
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'inactive', 'draft' => 'gray',
                        'out_of_stock' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('badges')
                    ->label('Highlights')
                    ->badge()
                    ->state(function ($record): array {
                        return collect([
                            'Featured' => $record->is_featured,
                            'New' => $record->is_new,
                            'Popular' => $record->is_popular,
                            'Trending' => $record->is_trending,
                            'Promo' => $record->is_promotional,
                        ])
                            ->filter()
                            ->keys()
                            ->values()
                            ->all();
                    }),

                IconColumn::make('requires_shipping')
                    ->label('Shipping')
                    ->boolean()
                    ->tooltip(fn ($record) => $record->requires_shipping
                        ? 'Requires shipping'
                        : 'Digital / no shipping required'
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->native(false)
                    ->options(ProductStatus::class),

                SelectFilter::make('product_type')
                    ->label('Product Type')
                    ->native(false)
                    ->options(ProductType::class),

                SelectFilter::make('is_featured')
                    ->label('Featured')
                    ->options([
                        1 => 'Featured',
                        0 => 'Not Featured',
                    ]),

                SelectFilter::make('requires_shipping')
                    ->label('Shipping')
                    ->options([
                        1 => 'Requires Shipping',
                        0 => 'No Shipping',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
