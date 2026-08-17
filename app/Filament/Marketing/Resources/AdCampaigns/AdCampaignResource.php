<?php

namespace App\Filament\Marketing\Resources\AdCampaigns;

use App\Filament\Marketing\Resources\AdCampaigns\Pages\CreateAdCampaign;
use App\Filament\Marketing\Resources\AdCampaigns\Pages\EditAdCampaign;
use App\Filament\Marketing\Resources\AdCampaigns\Pages\ListAdCampaigns;
use App\Filament\Marketing\Resources\AdCampaigns\Pages\ViewAdCampaign;
use App\Filament\Marketing\Resources\AdCampaigns\RelationManagers\AdsRelationManager;
use App\Filament\Marketing\Resources\AdCampaigns\Schemas\AdCampaignForm;
use App\Filament\Marketing\Resources\AdCampaigns\Schemas\AdCampaignInfolist;
use App\Filament\Marketing\Resources\AdCampaigns\Tables\AdCampaignsTable;
use App\Models\AdCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AdCampaignResource extends Resource
{
    protected static ?string $model = AdCampaign::class;

    protected static ?string $navigationLabel = 'Ad Campaigns';

    protected static string|UnitEnum|null $navigationGroup = 'Advert Management';

    protected static string|BackedEnum|null $navigationIcon = 'icon-ad-campaign';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdCampaignForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdCampaignInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AdsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdCampaigns::route('/'),
            // 'create' => CreateAdCampaign::route('/create'),
            // 'view' => ViewAdCampaign::route('/{record}'),
            // 'edit' => EditAdCampaign::route('/{record}/edit'),
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
