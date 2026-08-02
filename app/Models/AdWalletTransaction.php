<?php

namespace App\Models;

use Database\Factories\AdWalletTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Table('ad_wallet_transactions')]
#[Fillable([
    'ad_wallet_id', 'type', 'amount', 'balance_after',
    'ad_wallet_top_up_id', 'ad_campaign_id', 'ad_view_id', 'ad_click_id',
    'description'
])]
class AdWalletTransaction extends Model
{
    /** @use HasFactory<AdWalletTransactionFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function adWallet(): BelongsTo
    {
        return $this->belongsTo(AdWallet::class);
    }

    public function adWalletTopUp(): BelongsTo
    {
        return $this->belongsTo(AdWalletTopUp::class);
    }

    public function adCampaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class);
    }

    public function adView(): BelongsTo
    {
        return $this->belongsTo(AdView::class);
    }

    public function adClicks(): BelongsTo
    {
        return $this->belongsTo(AdClick::class);
    }
}
