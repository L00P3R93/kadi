<?php

namespace App\Filament\Resources\RedemptionTransactions\Schemas;

use App\Enums\RedemptionTransactionDirection;
use App\Enums\RedemptionTransactionStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RedemptionTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('order_id')
                    ->relationship('order', 'id'),
                Select::make('order_item_id')
                    ->relationship('orderItem', 'id'),
                TextInput::make('source')
                    ->required()
                    ->default('kadi_api'),
                Select::make('direction')
                    ->options(RedemptionTransactionDirection::class)
                    ->required(),
                TextInput::make('coin_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_before')
                    ->numeric(),
                TextInput::make('balance_after')
                    ->numeric(),
                TextInput::make('kadi_reference'),
                TextInput::make('idempotency_key')
                    ->required(),
                Select::make('status')
                    ->options(RedemptionTransactionStatus::class)
                    ->default('pending')
                    ->required(),
                Textarea::make('reason')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
            ]);
    }
}
