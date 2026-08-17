<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Enums\PromotionPriority;
use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('type')
                    ->options(PromotionType::class)
                    ->default('percentage_discount')
                    ->required(),
                Select::make('status')
                    ->options(PromotionStatus::class)
                    ->default('active')
                    ->required(),
                Select::make('priority')
                    ->options(PromotionPriority::class)
                    ->required()
                    ->default(0),
                TextInput::make('usage_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('per_user_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
                TextInput::make('rules')
                    ->required(),
            ]);
    }
}
