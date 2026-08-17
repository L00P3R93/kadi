<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderFulfillmentState;
use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('order_number')
                    ->required(),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default('pending')
                    ->required(),
                Select::make('payment_state')
                    ->options(OrderPaymentState::class)
                    ->default('unpaid')
                    ->required(),
                Select::make('fulfillment_state')
                    ->options(OrderFulfillmentState::class)
                    ->default('not_applicable')
                    ->required(),
                Select::make('payment_method')
                    ->options(PaymentMethod::class)
                    ->required(),
                TextInput::make('currency')
                    ->required()
                    ->default('KES'),
                TextInput::make('subtotal_money')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount_money')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_money')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_money')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('grand_total_money')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('subtotal_coins')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_coins')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('grand_total_coins')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('requires_shipping')
                    ->required(),
                DateTimePicker::make('payment_due_at'),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('cancelled_at'),
                DateTimePicker::make('completed_at'),
                Textarea::make('cancellation_reason')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
            ]);
    }
}
