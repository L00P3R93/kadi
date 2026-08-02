<?php

namespace App\Livewire\Ads;

use App\Models\AdCampaign;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[Title('Campaign Details | Kadi Kings')]
class ShowCampaign extends Component
{
    public AdCampaign $campaign;

    public function mount(AdCampaign $campaign): void
    {
        $user = auth()->user();

        // Mirrors AdCampaign::scopeUserCampaign() — admins see any campaign,
        // everyone else only their own. Done as a manual guard rather than a
        // Rule::in()/policy lookup since I don't have a CampaignPolicy to
        // confirm exists; if you already have one, $this->authorize('view',
        // $campaign) is the more idiomatic swap for this.
        if (! $user->isAdmin() && $campaign->ad_profile_id !== $user->adProfile?->id) {
            throw new AccessDeniedHttpException;
        }

        $this->campaign = $campaign->load('adCategory', 'adProfile');
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.ads.show-campaign')->layout('layouts.app');
    }
}
