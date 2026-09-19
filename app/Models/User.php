<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'account_no', 'phone', 'linked_id', 'google_id', 'avatar'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'age_confirmed_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Consent columns for the current terms version. Not mass-assignable;
     * apply with forceFill() so consent can only be set by our own code.
     *
     * @return array<string, mixed>
     */
    public static function consentAttributes(): array
    {
        return [
            'age_confirmed_at' => now(),
            'terms_accepted_at' => now(),
            'terms_version' => config('kadi.terms_version'),
        ];
    }

    public function hasCurrentConsent(): bool
    {
        return $this->age_confirmed_at !== null
            && $this->terms_accepted_at !== null
            && $this->terms_version === config('kadi.terms_version');
    }

    public function recordConsent(): void
    {
        $this->forceFill(static::consentAttributes())->save();
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
}
