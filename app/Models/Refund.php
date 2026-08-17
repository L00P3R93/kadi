<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Database\Factories\RefundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('refunds')]
#[Fillable([
    'payment_id', 'order_id',
    'amount_money', 'amount_coins',
    'reason',
    'reference', 'status',
    'initiated_by',
    'metadata',
])]
class Refund extends Model
{
    /** @use HasFactory<RefundFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount_money' => 'decimal:2',
            'amount_coins' => 'integer',
            'status' => RefundStatus::class,
            'metadata' => 'array',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
