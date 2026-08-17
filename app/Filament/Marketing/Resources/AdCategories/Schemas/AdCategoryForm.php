<?php

namespace App\Filament\Marketing\Resources\AdCategories\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class AdCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->prefixIcon('icon-ad-category')
                            ->prefixIconColor('primary')
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('key', Str::slug($state));
                            })
                            ->required(),
                        TextInput::make('key')
                            ->prefixIcon('icon-ad-category')
                            ->prefixIconColor('primary')
                            ->unique(ignoreRecord: true)
                            ->dehydrated(fn ($state) => filled($state)) // only send if filled
                            ->default(fn (callable $get) => function () use ($get) {
                                $name = $get('name');
                                if (filled($name)) {
                                    return Str::slug($name);
                                }

                                return null;
                            })
                            ->disabled()
                            ->required(),
                        TextInput::make('pricing_multiplier')
                            ->prefixIcon(Heroicon::OutlinedHashtag)
                            ->prefixIconColor('primary')
                            ->required()
                            ->numeric()
                            ->default(1.0),
                    ])->columns(3)->columnSpanFull(),

                    Section::make()->schema([
                        Toggle::make('requires_approval')
                            ->default(true)
                            ->required(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ])->columns(2)->columnSpanFull(),
                    Section::make('Ad Category Descriptions')->schema([
                        MarkdownEditor::make('description')
                            ->columnSpanFull(),
                    ])->collapsed()->columnSpanFull(),
                ])->columns(2)->columnSpan(['lg' => 3]),
            ]);
    }
}
