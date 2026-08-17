<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'account_no', 'phone', 'linked_id', 'google_id', 'avatar'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
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

    /**
     * Set the phone attribute - convert 0|+254 prefix to 254 for storage
     */
    public function setPhoneAttribute($value): void
    {
        $phone = trim($value);
        // If phone starts with +254, replace with 254
        if (str_starts_with($phone, '+254')) {
            $phone = '254'.substr($phone, 4);
        }
        // If phone starts with 0, replace with 254
        elseif (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        }
        $this->attributes['phone'] = $phone;
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->hasVerifiedEmail()) {
            return false;
        }

        if ($panel->getId() === 'console') {
            return $this->isAdmin();
        }

        if ($panel->getId() === 'marketing') {
            return $this->hasAnyRole(['super-admin', 'admin', 'player']);
        }

        return false;
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

    public function adProfile(): HasOne
    {
        return $this->hasOne(AdProfile::class);
    }

    public function adWalletTopUps(): HasMany
    {
        return $this->hasMany(AdWalletTopUp::class);
    }

    public function adCampaigns(): HasMany
    {
        return $this->hasMany(AdCampaign::class);
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

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'performed_by');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function promotionUsages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderStatusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentIdempotencyKeys(): HasMany
    {
        return $this->hasMany(PaymentIdempotencyKey::class);
    }

    public function redemptionTransactions(): HasMany
    {
        return $this->hasMany(RedemptionTransaction::class);
    }
}
