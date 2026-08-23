<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                 * ============================================================
                 * PRODUCT INFORMATION
                 * ============================================================
                 */

                Section::make('Product Information')
                    ->description('Basic information used to identify and categorize the product.')
                    ->icon(Heroicon::OutlinedCube)
                    ->schema([
                        TextInput::make('name')
                            ->label('Product Name')
                            ->prefixIcon(Heroicon::OutlinedTag)
                            ->prefixIconColor('primary')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Gaming Headset'),

                        Select::make('product_category_id')
                            ->label('Category')
                            ->prefixIcon(Heroicon::OutlinedFolder)
                            ->prefixIconColor('primary')
                            ->relationship('productCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->placeholder('Select a category'),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->prefixIcon(Heroicon::OutlinedQrCode)
                            ->prefixIconColor('primary')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. HEADSET-001'),

                        Select::make('product_type')
                            ->label('Product Type')
                            ->prefixIcon(Heroicon::OutlinedCube)
                            ->prefixIconColor('primary')
                            ->options(ProductType::class)
                            ->native(false)
                            ->default(ProductType::REWARD)
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->prefixIcon(Heroicon::OutlinedCheckCircle)
                            ->prefixIconColor('primary')
                            ->options(ProductStatus::class)
                            ->native(false)
                            ->default(ProductStatus::ACTIVE)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * PRICING
                 * ============================================================
                 */

                Section::make('Pricing')
                    ->description('Configure the product price in money and wallet coins.')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextInput::make('money_price')
                            ->label('Money Price')
                            ->prefixIcon(Heroicon::OutlinedBanknotes)
                            ->prefixIconColor('success')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('KES')
                            ->default(0)
                            ->required(),

                        TextInput::make('coin_price')
                            ->label('Coin Price')
                            ->prefixIcon(Heroicon::OutlinedCircleStack)
                            ->prefixIconColor('warning')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->suffix('coins')
                            ->default(0)
                            ->required(),

                        TextInput::make('original_money_price')
                            ->label('Original Money Price')
                            ->prefixIcon(Heroicon::OutlinedBanknotes)
                            ->prefixIconColor('gray')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('KES')
                            ->default(0)
                            ->helperText('Used when displaying a discounted price.'),

                        TextInput::make('original_coin_price')
                            ->label('Original Coin Price')
                            ->prefixIcon(Heroicon::OutlinedCircleStack)
                            ->prefixIconColor('gray')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->suffix('coins')
                            ->default(0)
                            ->helperText('Used when displaying a discounted coin price.'),

                        /*
                         * SYSTEM FIELD:
                         * currency
                         *
                         * This should be populated by the application rather
                         * than manually entered by an administrator.
                         *
                         * Current default currency appears to be KES.
                         */
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * INVENTORY
                 * ============================================================
                 */

                Section::make('Inventory')
                    ->description('Manage available stock and low-stock notifications.')
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->schema([
                        TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->prefixIcon(Heroicon::OutlinedArchiveBox)
                            ->prefixIconColor('primary')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('low_stock_threshold')
                            ->label('Low Stock Threshold')
                            ->prefixIcon(Heroicon::OutlinedExclamationTriangle)
                            ->prefixIconColor('warning')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(5)
                            ->required(),

                        /*
                         * SYSTEM FIELD:
                         * reserved_quantity
                         *
                         * This should be maintained automatically by your
                         * cart/order/inventory logic.
                         *
                         * Do not allow administrators to manually edit it.
                         */
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * MERCHANDISING
                 * ============================================================
                 */

                Section::make('Merchandising')
                    ->description('Control how the product is promoted and displayed in the store.')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->schema([
                        Toggle::make('is_featured')
                            ->label('Featured')
                            ->helperText('Show in featured product sections.')
                            ->default(false),

                        Toggle::make('is_new')
                            ->label('New')
                            ->helperText('Mark this product as new.')
                            ->default(false),

                        Toggle::make('is_popular')
                            ->label('Popular')
                            ->helperText('Show in popular product sections.')
                            ->default(false),

                        Toggle::make('is_trending')
                            ->label('Trending')
                            ->helperText('Show in trending product sections.')
                            ->default(false),

                        Toggle::make('is_promotional')
                            ->label('Promotional')
                            ->helperText('Mark this product as a promotional item.')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * PURCHASE & FULFILLMENT
                 * ============================================================
                 */

                Section::make('Purchase & Fulfillment')
                    ->description('Configure supported payment methods and delivery requirements.')
                    ->icon(Heroicon::OutlinedShoppingCart)
                    ->schema([
                        Toggle::make('is_redeemable_with_coins')
                            ->label('Redeemable with Coins')
                            ->helperText('Allow customers to purchase this product using wallet coins.')
                            ->default(true),

                        Toggle::make('is_purchasable_with_money')
                            ->label('Purchasable with Money')
                            ->helperText('Allow customers to purchase this product using money.')
                            ->default(true),

                        Toggle::make('requires_shipping')
                            ->label('Requires Shipping')
                            ->helperText('The product requires physical delivery.')
                            ->default(false),

                        TextInput::make('estimated_value')
                            ->label('Estimated Value')
                            ->prefixIcon(Heroicon::OutlinedBanknotes)
                            ->prefixIconColor('gray')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('KES')
                            ->helperText('Estimated monetary value of the product.'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * PRODUCT CONTENT
                 * ============================================================
                 */

                Section::make('Product Content')
                    ->description('Add the information customers will see when viewing the product.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextInput::make('short_description')
                            ->label('Short Description')
                            ->prefixIcon(Heroicon::OutlinedBars3)
                            ->prefixIconColor('primary')
                            ->maxLength(500)
                            ->placeholder('A short summary of the product.')
                            ->columnSpanFull(),

                        MarkdownEditor::make('description')
                            ->label('Description')
                            ->placeholder('Describe the product in detail...')
                            ->columnSpanFull(),

                        KeyValue::make('specifications')
                            ->label('Specifications')
                            ->keyLabel('Attribute')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Specification')
                            ->keyPlaceholder('e.g. Brand')
                            ->valuePlaceholder('e.g. Logitech')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * ADVANCED / SYSTEM DATA
                 * ============================================================
                 */

                Section::make('Advanced Data')
                    ->description('Additional structured data used internally by the application.')
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Metadata')
                            ->keyPlaceholder('e.g. source')
                            ->valuePlaceholder('e.g. campaign')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpan(['lg' => 3]),

                /*
                 * ============================================================
                 * SYSTEM-MANAGED FIELDS — REMOVE FROM FORM
                 * ============================================================
                 *
                 * slug
                 * currency
                 * reserved_quantity
                 *
                 * These should be populated/managed by application logic.
                 *
                 * Also don't expose:
                 *
                 * created_at
                 * updated_at
                 * deleted_at
                 *
                 * Those are Eloquent/system timestamps.
                 */
            ]);
    }
}
