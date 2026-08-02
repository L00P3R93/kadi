<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'account_no', 'phone', 'linked_id', 'google_id', 'avatar'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isLinked(): bool
    {
        return ! is_null($this->linked_id);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function adProfile(): BelongsTo
    {
        return $this->belongsTo(AdProfile::class);
    }

    public function adWalletTopUps(): HasMany
    {
        return $this->hasMany(AdWalletTopUp::class);
    }

    public function adCampaignReviews(): HasMany
    {
        return $this->hasMany(AdCampaign::class, 'reviewed_by');
    }

    public function adCampaignModerationLogs(): HasMany
    {
        return $this->hasMany(AdCampaignModerationLog::class, 'performed_by');
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
