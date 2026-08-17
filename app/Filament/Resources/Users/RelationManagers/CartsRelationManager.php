<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Carts\CartResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CartsRelationManager extends RelationManager
{
    protected static string $relationship = 'carts';

    protected static ?string $relatedResource = CartResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
