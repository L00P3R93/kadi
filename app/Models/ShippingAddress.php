<?php

namespace App\Models;

use Database\Factories\ShippingAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('shipments')]
#[Fillable([
    'order_id',
    'recipient_name', 'phone', 'email',
    'address_line_1', 'address_line_2', 'city', 'county', 'country', 'postal_code',
    'delivery_notes',
])]
class ShippingAddress extends Model
{
    /** @use HasFactory<ShippingAddressFactory> */
    use HasFactory, SoftDeletes;

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}
