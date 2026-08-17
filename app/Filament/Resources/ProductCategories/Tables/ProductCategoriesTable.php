<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use App\Models\ProductCategory;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (ProductCategory $record) => Str::upper($record->name))
                    ->weight(FontWeight::Medium),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Visibility')
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
                //
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->link()
                    ->icon('hugeicons-activity-04')
                    ->color(fn (ProductCategory $record): string => $record->is_active ? 'success' : 'gray')
                    ->label(fn (ProductCategory $record): string => $record->is_active ? 'Hide' : 'Show')
                    ->action(fn (ProductCategory $record) => $record->updateOrFail(['is_active' => ! $record->is_active])),
                EditAction::make()->iconButton()->icon(Heroicon::OutlinedPencilSquare)->color('warning')->tooltip('Edit'),
                ViewAction::make()->iconButton()->icon(Heroicon::OutlinedEye)->color('primary')->tooltip('View'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (): void {
                            Notification::make()
                                ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }
}
