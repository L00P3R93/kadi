<?php

namespace App\Filament\Resources\Refunds\Schemas;

use App\Enums\RefundStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('payment_id')
                    ->relationship('payment', 'id'),
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->required(),
                TextInput::make('amount_money')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('amount_coins')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('reason')
                    ->columnSpanFull(),
                TextInput::make('reference'),
                Select::make('status')
                    ->options(RefundStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('initiated_by')
                    ->numeric(),
                TextInput::make('metadata'),
            ]);
    }
}
