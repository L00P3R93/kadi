<?php

namespace App\Models;

use Database\Factories\AdCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Table('ad_categories')]
#[Fillable(['key', 'name', 'description', 'pricing_multiplier', 'requires_approval', 'is_active'])]
class AdCategory extends Model
{
    /** @use HasFactory<AdCategoryFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'pricing_multiplier' => 'decimal:2',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
