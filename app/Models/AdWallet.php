<?php

namespace App\Models;

use Database\Factories\AdWalletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['ad_profile_id', 'balance', 'currency'])]
class AdWallet extends Model
{
    /** @use HasFactory<AdWalletFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function adProfile(): BelongsTo
    {
        return $this->belongsTo(AdProfile::class);
    }

    public function adWalletTopUps(): HasMany
    {
        return $this->hasMany(AdWalletTopUp::class);
    }
}
