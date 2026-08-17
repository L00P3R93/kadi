<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderFulfillmentState;
use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                 * ============================================================
                 * ORDER OVERVIEW
                 * ============================================================
                 */

                Section::make('Order Overview')
                    ->description('Customer and current order status.')
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order Number')
                            ->weight('bold')
                            ->copyable()
                            ->copyMessage('Order number copied')
                            ->icon(Heroicon::OutlinedHashtag),

                        TextEntry::make('user.name')
                            ->label('Customer')
                            ->weight('medium')
                            ->icon(Heroicon::OutlinedUser)
                            ->placeholder('Guest'),

                        TextEntry::make('status')
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
                            }),

                        TextEntry::make('payment_state')
                            ->label('Payment Status')
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
                            }),

                        TextEntry::make('fulfillment_state')
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
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                 * ============================================================
                 * PAYMENT & FULFILLMENT
                 * ============================================================
                 */

                Section::make('Payment & Fulfillment')
                    ->description('How the order is being paid and fulfilled.')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->badge()
                            ->formatStateUsing(
                                fn ($state) => $state instanceof PaymentMethod
                                    ? str($state->value)->headline()
                                    : str($state)->headline()
                            ),

                        TextEntry::make('currency')
                            ->label('Currency')
                            ->badge(),

                        IconEntry::make('requires_shipping')
                            ->label('Shipping Required')
                            ->boolean()
                            ->trueIcon(Heroicon::OutlinedTruck)
                            ->falseIcon(Heroicon::OutlinedNoSymbol)
                            ->trueColor('success')
                            ->falseColor('gray'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                /*
                 * ============================================================
                 * MONEY SUMMARY
                 * ============================================================
                 */

                Section::make('Money Summary')
                    ->description('Breakdown of the monetary value of this order.')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextEntry::make('subtotal_money')
                            ->label('Subtotal')
                            ->money(fn (Order $record) => $record->currency ?? 'KES'),

                        TextEntry::make('discount_money')
                            ->label('Discount')
                            ->money(fn (Order $record) => $record->currency ?? 'KES')
                            ->color('danger'),

                        TextEntry::make('shipping_money')
                            ->label('Shipping')
                            ->money(fn (Order $record) => $record->currency ?? 'KES'),

                        TextEntry::make('tax_money')
                            ->label('Tax')
                            ->money(fn (Order $record) => $record->currency ?? 'KES'),

                        TextEntry::make('grand_total_money')
                            ->label('Grand Total')
                            ->money(fn (Order $record) => $record->currency ?? 'KES')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),

                /*
                 * ============================================================
                 * COIN SUMMARY
                 * ============================================================
                 */

                Section::make('Coin Summary')
                    ->description('Wallet coin value used for this order.')
                    ->icon(Heroicon::OutlinedCircleStack)
                    ->schema([
                        TextEntry::make('subtotal_coins')
                            ->label('Coin Subtotal')
                            ->numeric()
                            ->suffix(' coins'),

                        TextEntry::make('discount_coins')
                            ->label('Coin Discount')
                            ->numeric()
                            ->suffix(' coins')
                            ->color('danger'),

                        TextEntry::make('grand_total_coins')
                            ->label('Coin Total')
                            ->numeric()
                            ->suffix(' coins')
                            ->weight('bold')
                            ->size('lg')
                            ->color('warning'),
                    ])
                    ->columns(1)
                    ->columnSpan(['lg' => 1]),

                /*
                 * ============================================================
                 * ORDER TIMELINE
                 * ============================================================
                 */

                Section::make('Order Timeline')
                    ->description('Important events throughout the order lifecycle.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        TextEntry::make('payment_due_at')
                            ->label('Payment Due')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not set'),

                        TextEntry::make('paid_at')
                            ->label('Paid At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not paid'),

                        TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not completed'),

                        TextEntry::make('cancelled_at')
                            ->label('Cancelled At')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('Not cancelled'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                /*
                 * ============================================================
                 * CANCELLATION
                 * ============================================================
                 */

                Section::make('Cancellation')
                    ->description('Reason this order was cancelled.')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->schema([
                        TextEntry::make('cancellation_reason')
                            ->label('Reason')
                            ->placeholder('No cancellation reason provided.')
                            ->columnSpanFull(),
                    ])
                    ->visible(
                        fn (Order $record): bool => $record->status instanceof OrderStatus
                            ? $record->status->value === 'cancelled'
                            : $record->status === 'cancelled'
                    )
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
                                fn (Order $record): bool => $record->trashed()
                            ),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
