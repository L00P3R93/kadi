<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderFulfillmentState;
use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
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

class OrdersTable
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

                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Order number copied')
                    ->description(fn ($record) => $record->user?->name ?? 'Unknown customer'
                    ),

                /*
                 * ============================================================
                 * TOTAL
                 * ============================================================
                 */

                TextColumn::make('grand_total_money')
                    ->label('Total')
                    ->money(fn ($record) => $record->currency ?? 'KES')
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->grand_total_coins > 0
                        ? number_format($record->grand_total_coins).' coins'
                        : null
                    ),

                /*
                 * ============================================================
                 * ORDER STATUS
                 * ============================================================
                 */

                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof OrderStatus
                            ? str($state->value)->headline()
                            : str($state)->headline()
                    )
                    ->color(fn ($state) => match (
                        $state instanceof OrderStatus
                            ? $state->value
                            : $state
                    ) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled', 'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                /*
                 * ============================================================
                 * PAYMENT
                 * ============================================================
                 */

                TextColumn::make('payment_state')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof OrderPaymentState
                            ? str($state->value)->headline()
                            : str($state)->headline()
                    )
                    ->color(fn ($state) => match (
                        $state instanceof OrderPaymentState
                            ? $state->value
                            : $state
                    ) {
                        'paid' => 'success',
                        'pending', 'unpaid' => 'warning',
                        'failed' => 'danger',
                        'refunded', 'partially_refunded' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                /*
                 * ============================================================
                 * PAYMENT METHOD
                 * ============================================================
                 */

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof PaymentMethod
                            ? str($state->value)->headline()
                            : str($state)->headline()
                    )
                    ->sortable(),

                /*
                 * ============================================================
                 * FULFILLMENT
                 * ============================================================
                 */

                TextColumn::make('fulfillment_state')
                    ->label('Fulfillment')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => $state instanceof OrderFulfillmentState
                            ? str($state->value)->headline()
                            : str($state)->headline()
                    )
                    ->color(fn ($state) => match (
                        $state instanceof OrderFulfillmentState
                            ? $state->value
                            : $state
                    ) {
                        'pending', 'not_applicable' => 'gray',
                        'processing' => 'info',
                        'shipped' => 'warning',
                        'delivered', 'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                /*
                 * ============================================================
                 * SHIPPING
                 * ============================================================
                 */

                IconColumn::make('requires_shipping')
                    ->label('Shipping')
                    ->boolean()
                    ->tooltip(fn ($record) => $record->requires_shipping
                        ? 'Requires shipping'
                        : 'No shipping required'
                    ),

                /*
                 * ============================================================
                 * PLACED
                 * ============================================================
                 */

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at?->diffForHumans()
                    ),

                /*
                 * ============================================================
                 * SECONDARY COLUMNS
                 * Hidden by default but available through the column
                 * selector when needed.
                 * ============================================================
                 */

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subtotal_money')
                    ->label('Subtotal')
                    ->money(fn ($record) => $record->currency ?? 'KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discount_money')
                    ->label('Discount')
                    ->money(fn ($record) => $record->currency ?? 'KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('shipping_money')
                    ->label('Shipping Cost')
                    ->money(fn ($record) => $record->currency ?? 'KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tax_money')
                    ->label('Tax')
                    ->money(fn ($record) => $record->currency ?? 'KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subtotal_coins')
                    ->label('Coin Subtotal')
                    ->numeric()
                    ->suffix(' coins')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('discount_coins')
                    ->label('Coin Discount')
                    ->numeric()
                    ->suffix(' coins')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('grand_total_coins')
                    ->label('Coin Total')
                    ->numeric()
                    ->suffix(' coins')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_due_at')
                    ->label('Payment Due')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('paid_at')
                    ->label('Paid')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cancelled_at')
                    ->label('Cancelled')
                    ->dateTime('d M Y, H:i')
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
                    ->label('Order Status')
                    ->options(OrderStatus::class),

                SelectFilter::make('payment_state')
                    ->label('Payment')
                    ->options(OrderPaymentState::class),

                SelectFilter::make('fulfillment_state')
                    ->label('Fulfillment')
                    ->options(OrderFulfillmentState::class),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options(PaymentMethod::class),

                SelectFilter::make('requires_shipping')
                    ->label('Shipping')
                    ->options([
                        1 => 'Requires Shipping',
                        0 => 'No Shipping',
                    ]),

                TrashedFilter::make(),
            ])

            /*
             * ================================================================
             * RECORD ACTIONS
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
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
