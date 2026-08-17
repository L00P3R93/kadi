<?php

namespace App\Models;

use App\Enums\PromotionPriority;
use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('promotions')]
#[Fillable([
    'name', 'slug', 'description',
    'type', 'status', 'priority',
    'usage_limit', 'per_user_limit',
    'starts_at', 'end_date',
    'rules',
])]
class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => PromotionType::class,
            'status' => PromotionStatus::class,
            'usage_limit' => 'integer',
            'per_user_limit' => 'integer',
            'priority' => PromotionPriority::class,
            'starts_at' => 'datetime',
            'end_date' => 'datetime',
            'rules' => 'array',
        ];
    }

    public function promotionUsages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function promotionProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_products', 'promotion_id', 'product_id');
    }

    public function promotionCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'promotion_categories', 'promotion_id', 'product_category_id');
    }
}
