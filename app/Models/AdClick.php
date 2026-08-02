<?php

namespace App\Models;

use Database\Factories\AdClickFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Table('ad_clicks')]
#[Fillable([
    'ad_view_id', 'ad_id', 'ad_campaign_id', 'user_id',
    'charge_amount', 'ad_wallet_transaction_id',
    'device_platform', 'app_version', 'country',
    'clicked_at',
])]
class AdClick extends Model
{
    /** @use HasFactory<AdClickFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function adView(): BelongsTo
    {
        return $this->belongsTo(AdView::class);
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
}
