<?php

namespace App\Filament\Resources\RedemptionTransactions;

use App\Filament\Resources\RedemptionTransactions\Pages\CreateRedemptionTransaction;
use App\Filament\Resources\RedemptionTransactions\Pages\EditRedemptionTransaction;
use App\Filament\Resources\RedemptionTransactions\Pages\ListRedemptionTransactions;
use App\Filament\Resources\RedemptionTransactions\Pages\ViewRedemptionTransaction;
use App\Filament\Resources\RedemptionTransactions\Schemas\RedemptionTransactionForm;
use App\Filament\Resources\RedemptionTransactions\Schemas\RedemptionTransactionInfolist;
use App\Filament\Resources\RedemptionTransactions\Tables\RedemptionTransactionsTable;
use App\Models\RedemptionTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RedemptionTransactionResource extends Resource
{
    protected static ?string $model = RedemptionTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'hugeicons-coins-01';

    protected static string|UnitEnum|null $navigationGroup = '🛒 Commerce';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'kadi_reference';

    public static function form(Schema $schema): Schema
    {
        return RedemptionTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RedemptionTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedemptionTransactionsTable::configure($table);
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
            'index' => ListRedemptionTransactions::route('/'),
            'create' => CreateRedemptionTransaction::route('/create'),
            'view' => ViewRedemptionTransaction::route('/{record}'),
            'edit' => EditRedemptionTransaction::route('/{record}/edit'),
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
