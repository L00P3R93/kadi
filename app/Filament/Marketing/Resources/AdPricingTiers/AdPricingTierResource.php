<?php

namespace App\Filament\Marketing\Resources\AdPricingTiers;

use App\Filament\Marketing\Resources\AdPricingTiers\Pages\CreateAdPricingTier;
use App\Filament\Marketing\Resources\AdPricingTiers\Pages\EditAdPricingTier;
use App\Filament\Marketing\Resources\AdPricingTiers\Pages\ListAdPricingTiers;
use App\Filament\Marketing\Resources\AdPricingTiers\Schemas\AdPricingTierForm;
use App\Filament\Marketing\Resources\AdPricingTiers\Tables\AdPricingTiersTable;
use App\Models\AdPricingTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AdPricingTierResource extends Resource
{
    protected static ?string $model = AdPricingTier::class;

    protected static ?string $navigationLabel = 'Ad Pricing Tiers';

    protected static string|UnitEnum|null $navigationGroup = 'Advert Management';

    protected static string|BackedEnum|null $navigationIcon = 'icon-ad-pricing-tier';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'duration_seconds';

    public static function form(Schema $schema): Schema
    {
        return AdPricingTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdPricingTiersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdPricingTiers::route('/'),
            // 'create' => CreateAdPricingTier::route('/create'),
            // 'edit' => EditAdPricingTier::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
