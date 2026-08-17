<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\MerchandisingCollections\MerchandisingCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MerchandisingCollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'merchandisingCollections';

    protected static ?string $relatedResource = MerchandisingCollectionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
