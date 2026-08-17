<?php

namespace App\Models;

use Database\Factories\PromotionUsageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('promotion_usages')]
#[Fillable([
    'promotion_id', 'user_id', 'order_id',
    'money_discount', 'coin_discount',
])]
class PromotionUsage extends Model
{
    /** @use HasFactory<PromotionUsageFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'money_discount' => 'decimal:2',
            'coin_discount' => 'integer',
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
