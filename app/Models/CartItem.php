<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('cart_items')]
#[Fillable([
    'cart_id', 'product_id', 'product_variant_id', 'quantity',
    'money_unit_price', 'coin_unit_price',
    'promotion_id',
    'metadata',
])]
class CartItem extends Model
{
    protected function casts(): array
    {
        return [
            'money_unit_price' => 'decimal:2',
            'coin_unit_price' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
