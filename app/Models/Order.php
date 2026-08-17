<?php

namespace App\Models;

use App\Enums\OrderFulfillmentState;
use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('orders')]
#[Fillable([
    'user_id', 'order_number',
    'status', 'payment_state', 'fulfillment_state',
    'payment_method', 'currency',
    'subtotal_money', 'discount_money', 'shipping_money', 'tax_money', 'grand_total_money',
    'subtotal_coins', 'discount_coins', 'grand_total_coins',
    'requires_shipping',
    'payment_due_at', 'paid_at', 'cancelled_at', 'completed_at', 'cancellation_reason',
    'metadata',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'payment_due_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'requires_shipping' => 'boolean',
            'subtotal_coins' => 'integer',
            'discount_coins' => 'integer',
            'grand_total_coins' => 'integer',
            'subtotal_money' => 'decimal:2',
            'discount_money' => 'decimal:2',
            'shipping_money' => 'decimal:2',
            'tax_money' => 'decimal:2',
            'grand_total_money' => 'decimal:2',

            // cast to Enums
            'payment_method' => PaymentMethod::class,
            'status' => OrderStatus::class,
            'payment_state' => OrderPaymentState::class,
            'fulfillment_state' => OrderFulfillmentState::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderStatusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function redemptionTransactions(): HasMany
    {
        return $this->hasMany(RedemptionTransaction::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
