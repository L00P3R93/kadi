<?php

namespace App\Models;

use Database\Factories\AdWalletTopUpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['ad_wallet_id', 'user_id', 'amount', 'phone_number', 'transaction_ref', 'completed_at', 'status'])]
class AdWalletTopUp extends Model
{
    /** @use HasFactory<AdWalletTopUpFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function adWallet(): BelongsTo
    {
        return $this->belongsTo(AdWallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
