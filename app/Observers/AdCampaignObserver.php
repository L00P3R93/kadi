<?php

namespace App\Observers;

use App\Models\AdCampaign;
use Illuminate\Support\Carbon;

class AdCampaignObserver
{
    public function creating(AdCampaign $adCampaign): void
    {
        $user = auth()->user();
        $adProfile = $user->adProfile;

        if ($adProfile) {
            $adCampaign->ad_profile_id = $adProfile->id;
            $adCampaign->user_id = $user->id;
        } else {
            $adProfile = $user->adProfile()->createQuietly([
                'company_name' => $user->name,
                'company_phone' => $user->phone,
                'company_email' => $user->email,
            ]);

            $adCampaign->ad_profile_id = $adProfile->id;
            $adCampaign->user_id = $user->id;
        }

        $adCampaign->escrowed_budget = $adCampaign->total_budget;
        $adCampaign->starts_at = Carbon::make("{$adCampaign->starts_at} 00:00:00");
        $adCampaign->ends_at = Carbon::make("{$adCampaign->ends_at} 23:59:59");
    }

    /**
     * Handle the AdCampaign "created" event.
     */
    public function created(AdCampaign $adCampaign): void
    {
        //
    }

    /**
     * Handle the AdCampaign "updated" event.
     */
    public function updated(AdCampaign $adCampaign): void
    {
        //
    }

    /**
     * Handle the AdCampaign "deleted" event.
     */
    public function deleted(AdCampaign $adCampaign): void
    {
        //
    }

    /**
     * Handle the AdCampaign "restored" event.
     */
    public function restored(AdCampaign $adCampaign): void
    {
        //
    }

    /**
     * Handle the AdCampaign "force deleted" event.
     */
    public function forceDeleted(AdCampaign $adCampaign): void
    {
        //
    }
}
