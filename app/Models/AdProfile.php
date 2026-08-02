<?php

namespace App\Models;

use Database\Factories\AdProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['user_id', 'company_name', 'company_phone', 'company_email', 'company_website', 'status'])]
class AdProfile extends Model
{
    /** @use HasFactory<AdProfileFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adWallet(): BelongsTo
    {
        return $this->belongsTo(AdWallet::class);
    }
}
