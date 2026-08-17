<?php

namespace App\Filament\Resources\Carts\Schemas;

use App\Enums\CartStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('currency')
                    ->required()
                    ->default('KES'),
                Select::make('status')
                    ->options(CartStatus::class)
                    ->default('active')
                    ->required(),
            ]);
    }
}
