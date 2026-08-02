<?php

namespace App\Models;

use Database\Factories\AdViewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Table('ad_views')]
#[Fillable([
    'ad_id', 'ad_campaign_id', 'user_id', 'status',
    'watched_percentage', 'reward_granted', 'reward_amount', 'reward_credited_to_game_wallet',
    'charge_amount', 'ad_wallet_transaction_id',
    'device_platform', 'app_version', 'country',
    'started_at', 'completed_at',
])]
class AdView extends Model
{
    /** @use HasFactory<AdViewFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'watched_percentage' => 'decimal:2',
            'reward_granted' => 'boolean',
            'reward_amount' => 'integer',
            'reward_credited_to_game_wallet' => 'boolean',
            'charge_amount' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function adCampaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adWalletTransaction(): BelongsTo
    {
        return $this->belongsTo(AdWalletTransaction::class);
    }

    public function adClicks(): HasMany
    {
        return $this->hasMany(AdClick::class);
    }

    public function adAnalyticEvents(): HasMany
    {
        return $this->hasMany(AdAnalyticEvent::class);
    }
}
