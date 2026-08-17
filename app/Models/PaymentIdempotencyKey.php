<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('payment_idempotency_keys')]
#[Fillable([
    'user_id', 'key', 'request_hash', 'response_status', 'response_payload', 'expires_at',
])]
class PaymentIdempotencyKey extends Model
{
    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'response_payload' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
