<?php

namespace App\Models;

use App\Enums\RedemptionTransactionDirection;
use App\Enums\RedemptionTransactionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('redemption_transactions')]
#[Fillable([
    'user_id', 'order_id', 'order_item_id', 'source',
    'direction',
    'coin_amount', 'balance_before', 'balance_after',
    'kadi_reference', 'idempotency_key',
    'status',
    'reason', 'metadata',
])]
class RedemptionTransaction extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'direction' => RedemptionTransactionDirection::class,
            'coin_amount' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'status' => RedemptionTransactionStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
