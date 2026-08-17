<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /*
                 * ============================================================
                 * ORDER
                 * ============================================================
                 */

                TextColumn::make('order.id')
                    ->label('Order')
                    ->prefix('#')
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->iconColor('primary')
                    ->sortable()
                    ->searchable(),

                /*
                 * ============================================================
                 * CUSTOMER
                 * ============================================================
                 */

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->icon(Heroicon::OutlinedUser)
                    ->iconColor('gray')
                    ->searchable()
                    ->sortable(),

                /*
                 * ============================================================
                 * PAYMENT
                 * ============================================================
                 */

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->icon(Heroicon::OutlinedWallet)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->icon(Heroicon::OutlinedHashtag)
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Payment reference copied')
                    ->copyMessageDuration(1500)
                    ->limit(24)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),

                /*
                 * ============================================================
                 * AMOUNT
                 * ============================================================
                 */

                TextColumn::make('amount')
                    ->label('Amount')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->prefix(fn ($record): string => $record->currency.' ')
                    ->sortable()
                    ->alignEnd(),

                /*
                 * ============================================================
                 * STATUS
                 * ============================================================
                 */

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                /*
                 * ============================================================
                 * TIMELINE
                 * ============================================================
                 */

                TextColumn::make('initiated_at')
                    ->label('Initiated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('failed_at')
                    ->label('Failed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                 * ============================================================
                 * TECHNICAL DATA
                 * ============================================================
                 */

                TextColumn::make('merchant_request_id')
                    ->label('Merchant Request ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('checkout_request_id')
                    ->label('Checkout Request ID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            /*
             * ================================================================
             * FILTERS
             * ================================================================
             */

            ->filters([
                SelectFilter::make('status')
                    ->label('Payment Status')
                    ->options(PaymentStatus::class),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options(PaymentMethod::class),

                SelectFilter::make('provider')
                    ->label('Provider')
                    ->options(fn () => Payment::query()
                        ->whereNotNull('provider')
                        ->where('provider', '!=', '')
                        ->distinct()
                        ->orderBy('provider')
                        ->pluck('provider', 'provider')
                        ->toArray()),

                TrashedFilter::make(),
            ])

            /*
             * ================================================================
             * ACTIONS
             * ================================================================
             */

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])

            /*
             * ================================================================
             * BULK ACTIONS
             * ================================================================
             */

            ->toolbarActions([
                BulkActionGroup::make([
                    //                    DeleteBulkAction::make(),
                    //                    ForceDeleteBulkAction::make(),
                    //                    RestoreBulkAction::make(),
                ]),
            ])

            ->defaultSort('created_at', 'desc');
    }
}
