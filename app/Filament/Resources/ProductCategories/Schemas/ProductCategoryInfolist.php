<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Models\ProductCategory;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Information')
                    ->description('Core information about this product category.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Category Name')
                            ->weight('bold')
                            ->size('lg')
                            ->icon('heroicon-o-tag')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('parent.name')
                            ->label('Parent Category')
                            ->icon('iconoir-folder')
                            ->placeholder('Root Category'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->icon('iconoir-link')
                            ->copyable()
                            ->copyMessage('Slug copied')
                            ->placeholder('-'),

                    ])
                    ->columns(2),

                Section::make('Category Details')
                    ->description('Secondary information about this product category.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                /*
                Section::make('Search Engine Optimization')
                    ->description('Metadata used when this category appears in search engines.')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema([
                        TextEntry::make('meta_title')
                            ->label('Meta Title')
                            ->icon('iconoir-page-edit')
                            ->placeholder('Not configured')
                            ->copyable(),

                        TextEntry::make('meta_description')
                            ->label('Meta Description')
                            ->icon('iconoir-text')
                            ->placeholder('Not configured')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                */
                Section::make('Record Information')
                    ->description('System information and record timestamps.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M j, Y · H:i')
                            ->icon('iconoir-calendar')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('M j, Y · H:i')
                            ->icon('iconoir-calendar-plus')
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime('M j, Y · H:i')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->placeholder('-')
                            ->visible(
                                fn (ProductCategory $record): bool => $record->trashed()
                            ),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
