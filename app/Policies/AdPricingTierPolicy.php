<?php

namespace App\Policies;

use App\Models\AdPricingTier;
use App\Models\User;

class AdPricingTierPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage ad categories');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AdPricingTier $adPricingTier): bool
    {
        return $user->hasPermissionTo('manage ad categories');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage ad categories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AdPricingTier $adPricingTier): bool
    {
        return $user->hasPermissionTo('manage ad categories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AdPricingTier $adPricingTier): bool
    {
        return $user->hasPermissionTo('manage ad categories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AdPricingTier $adPricingTier): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AdPricingTier $adPricingTier): bool
    {
        return false;
    }
}
