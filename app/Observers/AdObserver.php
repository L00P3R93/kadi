<?php

namespace App\Observers;

use App\Models\Ad;
use App\Models\AdPricingTier;

class AdObserver
{
    public function creating(Ad $ad): void
    {
        $campaign = $ad->adCampaign;
        $category = $campaign->category;
        $pricing_multiplier = $category->pricing_multiplier;
        $pricingTier = AdPricingTier::query()->where('duration_seconds', $ad->duration_seconds)->first();
        $base_cost = $pricingTier->base_cost;
        $cost_per_view = $base_cost * $pricing_multiplier;
        $ad->cost_per_view = $cost_per_view;
        $ad->cost_per_click = 1.00;
        $ad->reward_message = 'Watch the full video to earn {amount} Coins.';
        $ad->reward_amount = 100;
        $ad->reward_type = 'coins';
    }

    /**
     * Handle the Ad "created" event.
     */
    public function created(Ad $ad): void
    {
        //
    }

    /**
     * Handle the Ad "updated" event.
     */
    public function updated(Ad $ad): void
    {
        //
    }

    /**
     * Handle the Ad "deleted" event.
     */
    public function deleted(Ad $ad): void
    {
        //
    }

    /**
     * Handle the Ad "restored" event.
     */
    public function restored(Ad $ad): void
    {
        //
    }

    /**
     * Handle the Ad "force deleted" event.
     */
    public function forceDeleted(Ad $ad): void
    {
        //
    }
}
