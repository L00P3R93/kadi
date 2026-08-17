<?php

namespace App\Filament\Marketing\Resources\AdCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AdCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', direction: 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Category')
                    ->formatStateUsing(function ($state) {
                        return Str::upper($state);
                    })
                    ->weight(FontWeight::Bold)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('key')
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('pricing_multiplier')
                    ->formatStateUsing(function ($state) {
                        $formatted = number_format($state, 2);

                        return "{$formatted}X";
                    })
                    ->badge()
                    ->sortable(),
                IconColumn::make('requires_approval')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->icon(Heroicon::OutlinedPencilSquare)->color('warning')->tooltip('Edit'),
                ViewAction::make()->iconButton()->icon(Heroicon::OutlinedEye)->color('primary')->tooltip('View'),
                DeleteAction::make()->iconButton()->icon(Heroicon::OutlinedTrash)->color('danger')->tooltip('Delete'),
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
