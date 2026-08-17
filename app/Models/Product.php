<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('products')]
#[Fillable([
    'product_category_id',
    'sku', 'name', 'slug', 'short_description', 'description', 'specifications',
    'product_type', 'status',
    'money_price', 'coin_price', 'original_money_price', 'original_coin_price', 'currency',
    'stock_quantity', 'reserved_quantity', 'low_stock_threshold',
    'is_featured', 'is_new', 'is_popular', 'is_trending', 'is_promotional',
    'estimated_value', 'requires_shipping', 'is_redeemable_with_coins', 'is_purchasable_with_money',
    'metadata',
])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'money_price' => 'decimal:2',
            'coin_price' => 'integer',
            'original_money_price' => 'decimal:2',
            'original_coin_price' => 'integer',
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_popular' => 'boolean',
            'is_trending' => 'boolean',
            'is_promotional' => 'boolean',
            'estimated_value' => 'decimal:2',
            'requires_shipping' => 'boolean',
            'is_redeemable_with_coins' => 'boolean',
            'is_purchasable_with_money' => 'boolean',
            'metadata' => 'array',

            // cast to Enums
            'status' => ProductStatus::class,
            'product_type' => ProductType::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function merchandisingCollections(): BelongsToMany
    {
        return $this->belongsToMany(MerchandisingCollection::class, 'merchandising_collection_product', 'product_id', 'merchandising_collection_id')->withTimestamps();
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products', 'product_id', 'promotion_id')->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
