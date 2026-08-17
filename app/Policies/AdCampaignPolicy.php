<?php

namespace App\Policies;

use App\Models\AdCampaign;
use App\Models\User;

class AdCampaignPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage ad campaigns');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AdCampaign $adCampaign): bool
    {
        return $user->hasPermissionTo('manage ad campaigns');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage ad campaigns');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AdCampaign $adCampaign): bool
    {
        return $user->hasPermissionTo('manage ad campaigns');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AdCampaign $adCampaign): bool
    {
        return $user->hasPermissionTo('manage ad campaigns');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AdCampaign $adCampaign): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AdCampaign $adCampaign): bool
    {
        return false;
    }
}
