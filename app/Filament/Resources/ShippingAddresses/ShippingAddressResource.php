<?php

namespace App\Filament\Resources\ShippingAddresses;

use App\Filament\Resources\ShippingAddresses\Pages\CreateShippingAddress;
use App\Filament\Resources\ShippingAddresses\Pages\EditShippingAddress;
use App\Filament\Resources\ShippingAddresses\Pages\ListShippingAddresses;
use App\Filament\Resources\ShippingAddresses\Pages\ViewShippingAddress;
use App\Filament\Resources\ShippingAddresses\Schemas\ShippingAddressForm;
use App\Filament\Resources\ShippingAddresses\Schemas\ShippingAddressInfolist;
use App\Filament\Resources\ShippingAddresses\Tables\ShippingAddressesTable;
use App\Models\ShippingAddress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ShippingAddressResource extends Resource
{
    protected static ?string $model = ShippingAddress::class;

    protected static string|BackedEnum|null $navigationIcon = 'iconoir-map-pin';

    protected static string|UnitEnum|null $navigationGroup = '🚚 Shipping & Fulfillment';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'recipient_name';

    public static function form(Schema $schema): Schema
    {
        return ShippingAddressForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShippingAddressInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShippingAddressesTable::configure($table);
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
            'index' => ListShippingAddresses::route('/'),
            'create' => CreateShippingAddress::route('/create'),
            'view' => ViewShippingAddress::route('/{record}'),
            'edit' => EditShippingAddress::route('/{record}/edit'),
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
