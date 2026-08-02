<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Database\Factories\AdCampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable([
    'ad_profile_id', 'ad_category_id', 'name', 'status', 'total_budget',
    'escrowed_budget', 'spent_budget', 'priority', 'frequency_cap', 'starts_at', 'ends_at',
    'reviewed_at', 'rejection_reason',
])]
#[Table('ad_campaigns')]
class AdCampaign extends Model
{
    /** @use HasFactory<AdCampaignFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'total_budget' => 'decimal:2',
            'escrowed_budget' => 'decimal:2',
            'spent_budget' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function scopeUserCampaign(Builder $query)
    {
        $user = auth()->user();
        return $user->isAdmin() ? $query : $query->where('ad_profile_id', $user->adProfile->id);
    }

    public function adProfile(): BelongsTo
    {
        return $this->belongsTo(AdProfile::class);
    }

    public function adCategory(): BelongsTo
    {
        return $this->belongsTo(AdCategory::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adWalletTransactions(): HasMany
    {
        return $this->hasMany(AdWalletTransaction::class);
    }

    public function adCampaignModerationLogs(): HasMany
    {
        return $this->hasMany(AdCampaignModerationLog::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }

    public function adViews(): HasMany
    {
        return $this->hasMany(AdView::class);
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
