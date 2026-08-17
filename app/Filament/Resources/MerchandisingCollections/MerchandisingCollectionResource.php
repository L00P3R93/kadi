<?php

namespace App\Filament\Resources\MerchandisingCollections;

use App\Filament\Resources\MerchandisingCollections\Pages\CreateMerchandisingCollection;
use App\Filament\Resources\MerchandisingCollections\Pages\EditMerchandisingCollection;
use App\Filament\Resources\MerchandisingCollections\Pages\ListMerchandisingCollections;
use App\Filament\Resources\MerchandisingCollections\Pages\ViewMerchandisingCollection;
use App\Filament\Resources\MerchandisingCollections\Schemas\MerchandisingCollectionForm;
use App\Filament\Resources\MerchandisingCollections\Schemas\MerchandisingCollectionInfolist;
use App\Filament\Resources\MerchandisingCollections\Tables\MerchandisingCollectionsTable;
use App\Models\MerchandisingCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class MerchandisingCollectionResource extends Resource
{
    protected static ?string $model = MerchandisingCollection::class;

    protected static string|BackedEnum|null $navigationIcon = 'iconoir-list-select';

    protected static string|UnitEnum|null $navigationGroup = '📣 Marketing';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MerchandisingCollectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerchandisingCollectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchandisingCollectionsTable::configure($table);
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
            'index' => ListMerchandisingCollections::route('/'),
            'create' => CreateMerchandisingCollection::route('/create'),
            'view' => ViewMerchandisingCollection::route('/{record}'),
            'edit' => EditMerchandisingCollection::route('/{record}/edit'),
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
