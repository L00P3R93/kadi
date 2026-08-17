<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                 * ============================================================
                 * PAYMENT INFORMATION
                 * ============================================================
                 */

                Section::make('Payment Information')
                    ->description('Order, customer, payment method and transaction amount.')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->prefixIcon(Heroicon::OutlinedShoppingBag)
                            ->prefixIconColor('primary')
                            ->relationship('order', 'id')
                            ->disabled()
                            ->dehydrated(false)
                            ->native(false)
                            ->placeholder('Order'),

                        Select::make('user_id')
                            ->label('Customer')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->prefixIconColor('primary')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->dehydrated(false)
                            ->native(false)
                            ->placeholder('Customer'),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->prefixIcon(Heroicon::OutlinedWallet)
                            ->prefixIconColor('primary')
                            ->options(PaymentMethod::class)
                            ->disabled()
                            ->dehydrated(false)
                            ->native(false),

                        TextInput::make('provider')
                            ->label('Payment Provider')
                            ->prefixIcon(Heroicon::OutlinedBuildingStorefront)
                            ->prefixIconColor('primary')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->prefixIcon(Heroicon::OutlinedBanknotes)
                            ->prefixIconColor('primary')
                            ->prefix('KES')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('currency')
                            ->label('Currency')
                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                            ->prefixIconColor('primary')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * PAYMENT STATUS
                 * ============================================================
                 */

                Section::make('Payment Status')
                    ->description('Current payment state and administrative failure information.')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->schema([
                        Select::make('status')
                            ->label('Payment Status')
                            ->prefixIcon(Heroicon::OutlinedCheckCircle)
                            ->prefixIconColor('primary')
                            ->options(PaymentStatus::class)
                            ->native(false)
                            ->required()
                            ->live(),

                        Textarea::make('failure_reason')
                            ->label('Failure Reason')
                            ->placeholder('Enter the reason for the payment failure...')
                            ->rows(3)
                            ->maxLength(1000)
                            ->required()
                            ->columnSpanFull()
                            ->visible(function (Get $get): bool {
                                $status = $get('status');

                                return $status instanceof PaymentStatus
                                    ? $status->value === 'failed'
                                    : $status === 'failed';
                            }),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * TRANSACTION REFERENCES
                 * ============================================================
                 */

                Section::make('Transaction References')
                    ->description('Identifiers generated by the payment provider and payment workflow.')
                    ->icon(Heroicon::OutlinedFingerPrint)
                    ->schema([
                        TextInput::make('reference')
                            ->label('Payment Reference')
                            ->prefixIcon(Heroicon::OutlinedHashtag)
                            ->prefixIconColor('primary')
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable(),

                        TextInput::make('merchant_request_id')
                            ->label('Merchant Request ID')
                            ->prefixIcon(Heroicon::OutlinedIdentification)
                            ->prefixIconColor('primary')
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable(),

                        TextInput::make('checkout_request_id')
                            ->label('Checkout Request ID')
                            ->prefixIcon(Heroicon::OutlinedQrCode)
                            ->prefixIconColor('primary')
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * PAYMENT TIMELINE
                 * ============================================================
                 */

                Section::make('Payment Timeline')
                    ->description('System-recorded timestamps for the payment lifecycle.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        TextInput::make('initiated_at')
                            ->label('Initiated At')
                            ->prefixIcon(Heroicon::OutlinedPlay)
                            ->prefixIconColor('primary')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('completed_at')
                            ->label('Completed At')
                            ->prefixIcon(Heroicon::OutlinedCheckCircle)
                            ->prefixIconColor('success')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('failed_at')
                            ->label('Failed At')
                            ->prefixIcon(Heroicon::OutlinedXCircle)
                            ->prefixIconColor('danger')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * ADVANCED DATA
                 * ============================================================
                 */

                Section::make('Advanced Data')
                    ->description('Additional structured information returned or stored during payment processing.')
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Metadata')
                            ->keyPlaceholder('e.g. mpesa_receipt')
                            ->valuePlaceholder('e.g. ABC123XYZ')
                            ->reorderable()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpan(['lg' => 3]),
            ]);

        /*
         * ============================================================
         * SYSTEM-MANAGED FIELDS
         * ============================================================
         *
         * order_id
         * user_id
         * payment_method
         * provider
         * reference
         * merchant_request_id
         * checkout_request_id
         *
         * amount
         * currency
         *
         * initiated_at
         * completed_at
         * failed_at
         *
         * metadata
         *
         * These values should be generated, calculated, or updated by
         * the payment/provider workflows and should not normally be
         * modified manually by administrators.
         *
         * ============================================================
         */
    }
}
