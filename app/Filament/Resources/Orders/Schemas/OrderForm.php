<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderFulfillmentState;
use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Order Information')
                    ->description('Customer and current order state.')
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->schema([
                        Select::make('user_id')
                            ->label('Customer')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->prefixIconColor('primary')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->placeholder('Select customer'),

                        Select::make('status')
                            ->label('Order Status')
                            ->prefixIcon(Heroicon::OutlinedCheckCircle)
                            ->prefixIconColor('primary')
                            ->options(OrderStatus::class)
                            ->native(false)
                            ->searchable()
                            ->default('pending')
                            ->live()
                            ->required(),

                        Select::make('payment_state')
                            ->label('Payment Status')
                            ->prefixIcon(Heroicon::OutlinedCreditCard)
                            ->prefixIconColor('primary')
                            ->options(OrderPaymentState::class)
                            ->native(false)
                            ->default('unpaid')
                            ->required(),

                        Select::make('fulfillment_state')
                            ->label('Fulfillment Status')
                            ->prefixIcon(Heroicon::OutlinedTruck)
                            ->prefixIconColor('primary')
                            ->options(OrderFulfillmentState::class)
                            ->native(false)
                            ->default('not_applicable')
                            ->required(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->prefixIcon(Heroicon::OutlinedWallet)
                            ->prefixIconColor('primary')
                            ->options(PaymentMethod::class)
                            ->native(false)
                            ->required(),

                        Toggle::make('requires_shipping')
                            ->label('Requires Shipping')
                            ->helperText('This order requires physical delivery.')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * CANCELLATION
                 * ============================================================
                 */

                Section::make('Cancellation')
                    ->description('A reason is required when cancelling an order.')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->schema([
                        Textarea::make('cancellation_reason')
                            ->label('Cancellation Reason')
                            ->placeholder('Enter the reason for cancelling this order...')
                            ->rows(3)
                            ->maxLength(1000)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->visible(function ($get): bool {
                        $status = $get('status');

                        return $status instanceof OrderStatus
                            ? $status->value === 'cancelled'
                            : $status === 'cancelled';
                    })
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * ADVANCED DATA
                 * ============================================================
                 */

                Section::make('Advanced Data')
                    ->description('Additional structured information stored with the order.')
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Metadata')
                            ->keyPlaceholder('e.g. source')
                            ->valuePlaceholder('e.g. checkout')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpan(['lg' => 3]),
            ]);

        /*
         * ============================================================
         * SYSTEM-MANAGED FIELDS — NOT PART OF THE FORM
         * ============================================================
         *
         * order_number
         * currency
         *
         * subtotal_money
         * discount_money
         * shipping_money
         * tax_money
         * grand_total_money
         *
         * subtotal_coins
         * discount_coins
         * grand_total_coins
         *
         * payment_due_at
         * paid_at
         * cancelled_at
         * completed_at
         *
         * These values should be generated, calculated, or updated
         * by the order/payment/fulfillment workflows.
         */
    }
}
