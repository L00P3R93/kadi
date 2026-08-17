<?php

namespace App\Models;

use App\Enums\ProductType;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('order_items')]
#[Fillable([
    'order_id', 'product_id', 'product_variant_id',
    'sku', 'product_name', 'product_type',
    'quantity',
    'unit_money_price', 'discount_money', 'subtotal_money',
    'unit_coin_price', 'discount_coins', 'subtotal_coins',
    'metadata',
])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,

            'quantity' => 'integer',
            'unit_money_price' => 'decimal:2',
            'discount_money' => 'decimal:2',
            'subtotal_money' => 'decimal:2',
            'unit_coin_price' => 'integer',
            'discount_coins' => 'integer',
            'subtotal_coins' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function redemptionTransactions(): HasMany
    {
        return $this->hasMany(RedemptionTransaction::class);
    }

    public function shipmentItems(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
