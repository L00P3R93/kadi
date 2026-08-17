<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Models\ProductCategory;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('Category Name')
                    ->prefixIcon( Heroicon::OutlinedTag)
                    ->prefixIconColor('primary')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                TextInput::make('slug')
                    ->label('Category Slug')
                    ->prefixIcon( Heroicon::OutlinedTag)
                    ->prefixIconColor('primary')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(ProductCategory::class, 'slug', ignoreRecord: true),

                Select::make('parent_id')
                    ->label('Parent Category')
                    ->prefixIcon( Heroicon::OutlinedArrowPathRoundedSquare)
                    ->prefixIconColor('primary')
                    ->relationship('parent', 'name', fn (Builder $query) => $query->where('parent_id', null))
                    ->searchable()
                    ->native(false)
                    ->placeholder('Select parent category')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Visibility')
                    ->default(true),

                Section::make('Product Category Description')->schema([
                    MarkdownEditor::make('description')->columnSpanFull(),
                ])->collapsed()->columnSpanFull(),

            ])->columns(2)->columnSpan(['lg' => 3]),
        ]);
    }
}
