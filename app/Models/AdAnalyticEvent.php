<?php

namespace App\Models;

use Database\Factories\AdAnalyticEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Table('ad_analytic_events')]
#[Fillable([
    'ad_id', 'ad_view_id', 'user_id', 'ad_campaign_id',
    'event_type', 'event_data',
    'device_platform', 'app_version', 'country',
    'occurred_at',
])]
class AdAnalyticEvent extends Model
{
    /** @use HasFactory<AdAnalyticEventFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'event_data' => 'array',
            'occurred_at' => 'datetime'
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function adView(): BelongsTo
    {
        return $this->belongsTo(AdView::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adCampaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class);
    }
}
