<?php

namespace App\Models;

use Database\Factories\AdPricingTierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['duration_seconds', 'base_cost'])]
class AdPricingTier extends Model
{
    /** @use HasFactory<AdPricingTierFactory> */
    use HasFactory, LogsActivity, SoftDeletes;
}
