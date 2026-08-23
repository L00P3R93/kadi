<?php

namespace App\Models;

use App\Enums\MerchandisingCollectionType;
use Database\Factories\MerchandisingCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('merchandising_collections')]
#[Fillable([
    'name', 'slug', 'type', 'description', 'sort_order', 'is_active', 'starts_at', 'ends_at',
])]
class MerchandisingCollection extends Model
{
    /** @use HasFactory<MerchandisingCollectionFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'type' => MerchandisingCollectionType::class,
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'merchandising_collection_product',
            'merchandising_collection_id',
            'product_id'
        )
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
