<?php

namespace App\Filament\Marketing\Resources\AdPricingTiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AdPricingTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('duration_seconds')
                        ->label('Duration (seconds)')
                        ->prefixIcon(Heroicon::OutlinedClock)
                        ->prefixIconColor('primary')
                        ->required()
                        ->numeric(),
                    TextInput::make('base_cost')
                        ->label('Base Cost')
                        ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                        ->prefixIconColor('primary')
                        ->required()
                        ->numeric()
                        ->prefix('KES'),
                ])->columns(2)->columnSpan(['lg' => 3]),
            ]);
    }
}
