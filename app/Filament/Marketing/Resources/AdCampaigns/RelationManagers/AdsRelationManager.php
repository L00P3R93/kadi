<?php

namespace App\Filament\Marketing\Resources\AdCampaigns\RelationManagers;

use App\Enums\CampaignStatus;
use App\Models\Ad;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AdsRelationManager extends RelationManager
{
    protected static string $relationship = 'ads';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Ad')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([

                        Tab::make('Content')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Ad Title')
                                    ->prefixIcon(Heroicon::OutlinedDocumentText)
                                    ->prefixIconColor('primary')
                                    ->required()
                                    ->maxLength(120)
                                    ->columnSpanFull(),

                                Toggle::make('is_active')
                                    ->label('Ad is active')
                                    ->helperText('Inactive ads are hidden from the rewarded-video rotation.')
                                    ->onIcon(Heroicon::OutlinedCheckCircle)
                                    ->offIcon(Heroicon::OutlinedXCircle)
                                    ->default(true)
                                    ->required(),
                                Section::make('Ad Description')->schema([
                                    MarkdownEditor::make('description')
                                        ->columnSpanFull(),
                                ])->collapsed()->columnSpanFull(),
                            ])->columns(2)->columnSpanFull(),

                        /*
                        Tab::make('Reward')
                            ->icon(Heroicon::OutlinedGift)
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('reward_type')
                                        ->options([
                                            'coins' => 'Coins',
                                        ])
                                        ->native(false)
                                        ->default('coins')
                                        ->required(),
                                    TextInput::make('reward_amount')
                                        ->numeric()
                                        ->minValue(0)
                                        ->suffix(fn (Get $get) => $get('reward_type') ?? 'coins')
                                        ->required(),
                                ]),
                                TextInput::make('reward_message')
                                    ->helperText('Shown to the player when the reward is granted, e.g. "You earned 50 coins!"')
                                    ->required()
                                    ->columnSpanFull(),
                                Toggle::make('reward_requires_completion')
                                    ->label('Require full playback before rewarding')
                                    ->helperText('If off, players are rewarded even if they skip early.')
                                    ->required(),
                            ]),
                        */

                        Tab::make('Media')
                            ->icon(Heroicon::OutlinedFilm)
                            ->schema([
                                Section::make('Video')
                                    ->icon(Heroicon::OutlinedVideoCamera)
                                    ->schema([
                                        Select::make('video_source')
                                            ->options([
                                                'url' => 'External URL',
                                                'upload' => 'Uploaded file',
                                            ])
                                            ->native(false)
                                            ->live()
                                            ->required(),
                                        TextInput::make('video_url')
                                            ->url()
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->visible(fn (Get $get) => $get('video_source') === 'url')
                                            ->required(fn (Get $get) => $get('video_source') === 'url')
                                            ->columnSpanFull(),
                                        FileUpload::make('video_storage_path')
                                            ->disk('public')
                                            ->directory('ads/videos')
                                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                                            ->visible(fn (Get $get) => $get('video_source') === 'upload')
                                            ->required(fn (Get $get) => $get('video_source') === 'upload')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Thumbnail')
                                    ->icon(Heroicon::OutlinedPhoto)
                                    ->schema([
                                        Select::make('thumbnail_source')
                                            ->options([
                                                'url' => 'External URL',
                                                'upload' => 'Uploaded file',
                                            ])
                                            ->native(false)
                                            ->live()
                                            ->required(),
                                        TextInput::make('thumbnail_url')
                                            ->url()
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->visible(fn (Get $get) => $get('thumbnail_source') === 'url')
                                            ->required(fn (Get $get) => $get('thumbnail_source') === 'url')
                                            ->columnSpanFull(),
                                        FileUpload::make('thumbnail_storage_path')
                                            ->disk('public')
                                            ->directory('ads/thumbnails')
                                            ->image()
                                            ->imageEditor()
                                            ->visible(fn (Get $get) => $get('thumbnail_source') === 'upload')
                                            ->required(fn (Get $get) => $get('thumbnail_source') === 'upload')
                                            ->columnSpanFull(),
                                    ]),
                                Select::make('duration_seconds')
                                    ->options([
                                        10 => '10 Seconds',
                                        20 => '20 Seconds',
                                        30 => '30 Seconds',
                                    ])
                                    ->suffix('sec')
                                    ->required(),
                                Select::make('orientation')
                                    ->options([
                                        'portrait' => 'Portrait',
                                        'landscape' => 'Landscape',
                                        'square' => 'Square',
                                    ])
                                    ->native(false)
                                    ->required(),
                            ]),

                        Tab::make('Call To Action')
                            ->icon(Heroicon::OutlinedCursorArrowRays)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('cta_text')
                                        ->helperText('Button label, e.g. "Play Now"')
                                        ->required(),
                                    TextInput::make('cta_subtitle')
                                        ->required(),
                                ]),
                                TextInput::make('click_url')
                                    ->url()
                                    ->prefixIcon(Heroicon::OutlinedLink)
                                    ->columnSpanFull(),
                                Toggle::make('skip_allowed')
                                    ->label('Allow players to skip')
                                    ->required(),
                            ]),

                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextEntry::make('title')->weight('bold'),
                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active'),
                    ])
                    ->columns(2),

                Section::make('Reward')
                    ->icon(Heroicon::OutlinedGift)
                    ->schema([
                        TextEntry::make('reward_amount')
                            ->numeric()
                            ->suffix(fn (Ad $record) => ' '.$record->reward_type),
                        TextEntry::make('reward_message'),
                        IconEntry::make('reward_requires_completion')
                            ->boolean()
                            ->label('Requires full playback'),
                    ])
                    ->columns(3),

                Section::make('Media')
                    ->icon(Heroicon::OutlinedFilm)
                    ->schema([
                        ImageEntry::make('thumbnail_url')
                            ->label('Thumbnail')
                            ->imageHeight(120)
                            ->defaultImageUrl(asset('casino/kadi.png')),
                        TextEntry::make('video_source')->badge(),
                        TextEntry::make('thumbnail_source')->badge(),
                        TextEntry::make('orientation')->badge(),
                    ])
                    ->columns(3),

                Section::make('Call To Action & Pricing')
                    ->icon(Heroicon::OutlinedCursorArrowRays)
                    ->schema([
                        TextEntry::make('cta_text'),
                        TextEntry::make('cta_subtitle'),
                        TextEntry::make('click_url')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('duration_seconds')->suffix(' sec'),
                        TextEntry::make('cost_per_view')->money('KES'),
                        TextEntry::make('cost_per_click')->money('KES'),
                        IconEntry::make('skip_allowed')->boolean(),
                    ])
                    ->columns(3),

                Section::make('Timestamps')
                    ->icon(Heroicon::OutlinedClock)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                        TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->dateTime()
                            ->visible(fn (Ad $record): bool => $record->trashed()),
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('')
                    ->square()
                    ->defaultImageUrl(asset('casino/kadi.png'))
                    ->imageSize(48),
                TextColumn::make('title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Ad $record) => $record->cta_text),
                TextColumn::make('orientation')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'portrait' => 'info',
                        'landscape' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state.'s')
                    ->sortable(),
                TextColumn::make('cost_per_view')
                    ->label('CPV')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('cost_per_click')
                    ->label('CPC')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('skip_allowed')
                    ->boolean()
                    ->label('Skippable')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active')
                    ->native(false),
                SelectFilter::make('orientation')
                    ->options([
                        'portrait' => 'Portrait',
                        'landscape' => 'Landscape',
                        'square' => 'Square',
                    ])
                    ->native(false),
                // TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::OutlinedPlus)
                    ->visible(fn () => $this->getOwnerRecord()->status === CampaignStatus::Active),
            ])
            ->recordActions([
                ViewAction::make()->iconButton()->icon(Heroicon::OutlinedEye)->color('primary')->tooltip('View'),
                EditAction::make()->iconButton()->icon(Heroicon::OutlinedPencilSquare)->color('warning')->tooltip('Edit'),
                DeleteAction::make()->iconButton()->icon(Heroicon::OutlinedTrash)->color('danger')->tooltip('Delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
