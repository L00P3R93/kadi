<?php

namespace App\Filament\Marketing\Resources\AdCategories;

use App\Filament\Marketing\Resources\AdCategories\Pages\CreateAdCategory;
use App\Filament\Marketing\Resources\AdCategories\Pages\EditAdCategory;
use App\Filament\Marketing\Resources\AdCategories\Pages\ListAdCategories;
use App\Filament\Marketing\Resources\AdCategories\Pages\ViewAdCategory;
use App\Filament\Marketing\Resources\AdCategories\Schemas\AdCategoryForm;
use App\Filament\Marketing\Resources\AdCategories\Schemas\AdCategoryInfolist;
use App\Filament\Marketing\Resources\AdCategories\Tables\AdCategoriesTable;
use App\Models\AdCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AdCategoryResource extends Resource
{
    protected static ?string $model = AdCategory::class;

    protected static ?string $navigationLabel = 'Ad Categories';

    protected static string|UnitEnum|null $navigationGroup = 'Advert Management';

    protected static string|BackedEnum|null $navigationIcon = 'icon-ad-category';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AdCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdCategoriesTable::configure($table);
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
            'index' => ListAdCategories::route('/'),
            // 'create' => CreateAdCategory::route('/create'),
            // 'view' => ViewAdCategory::route('/{record}'),
            // 'edit' => EditAdCategory::route('/{record}/edit'),
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
