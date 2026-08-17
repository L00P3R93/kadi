<?php

namespace App\Filament\Marketing\Resources\AdCampaigns\Schemas;

use App\Enums\CampaignPriority;
use App\Enums\CampaignStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class AdCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $adProfileId = $user->adProfile->first()->id;

        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default($user->id),
                Hidden::make('ad_profile_id')
                    ->default($adProfileId),
                Section::make('Campaign Details')->schema([
                    TextInput::make('name')
                        ->label('Campaign Name')
                        ->prefixIcon('icon-ad-campaign')
                        ->prefixIconColor('primary')
                        ->required(),
                    Select::make('ad_category_id')
                        ->label('Category')
                        ->prefixIcon('icon-ad-category')
                        ->prefixIconColor('primary')
                        ->relationship(
                            name: 'adCategory',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->limit(10)->orderBy('name')
                        )
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('status')
                        ->prefixIcon('hugeicons-status')
                        ->prefixIconColor('primary')
                        ->options(CampaignStatus::class)
                        ->default('draft')
                        ->native(false)
                        ->disabled(fn (): bool => ! auth()->user()->isAdmin())
                        ->required(),
                    Select::make('priority')
                        ->prefixIcon(Heroicon::OutlinedShieldExclamation)
                        ->prefixIconColor('primary')
                        ->required()
                        ->options(CampaignPriority::class)
                        ->native(false)
                        ->default(1),
                ])->columns(2)->columnSpanFull(),
                Section::make('Campaign Budget')->schema([
                    TextInput::make('total_budget')
                        ->prefixIcon('heroicon-s-wallet')
                        ->prefixIconColor('primary')
                        ->required()
                        ->numeric()
                        ->prefix('KES')
                        ->default(0.0),
                ])->columns(2)->columnSpanFull(),
                Section::make('Campaign Timeline')->schema([
                    DatePicker::make('starts_at')
                        ->prefixIcon('hugeicons-calendar-check-out-01')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->default(now()->addDays(1))
                        ->required(),
                    DatePicker::make('ends_at')
                        ->prefixIcon('iconoir-calendar-check')
                        ->prefixIconColor('primary')
                        ->native(false)
                        ->rules([
                            'after:starts_at',
                        ])
                        ->required(),
                ])->columns(2)->columnSpanFull(),
            ]);
    }
}
