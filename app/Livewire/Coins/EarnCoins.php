<?php

namespace App\Livewire\Coins;

use App\Enums\CampaignStatus;
use App\Models\Ad;
use App\Models\AdAnalyticEvent;
use App\Models\AdCampaign;
use App\Models\AdClick;
use App\Models\AdView;
use App\Models\AdWallet;
use App\Models\AdWalletTransaction;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Earn Coins | Kadi Kings')]
class EarnCoins extends Component
{
    /** @var Collection<int, Ad> Today's randomly-selected ads (up to 5). */
    public Collection $ads;

    /** @var array<int> IDs backing — cached per user, per day. */
    public array $selectedAdIds = [];

    /** @var array<int> Ad IDs this user has already completed today. */
    public array $watchedAdIds = [];

    public int $dailyCap = 0;

    public ?string $earnError = null;

    // ── Active watch session ──────────────────────────────────────────
    public ?Ad $activeAd = null;

    public ?int $activeViewId = null;

    public bool $playing = false;

    public bool $viewCompleted = false;

    public ?int $rewardEarned = null;

    public function mount(): void
    {
        $eligible = $this->eligibleAds();

        $this->selectedAdIds = Cache::remember(
            $this->selectionCacheKey(),
            now()->endOfDay(),
            fn () => $eligible->shuffle()->take(5)->pluck('id')->values()->all()
        );

        // The cached selection might reference ads/campaigns that stopped
        // being eligible since this morning (paused, exhausted budget) —
        // filter defensively rather than trusting the cache blindly. Full
        // re-validation happens again at watch-start and at completion.
        $this->ads = Ad::with(['adCampaign.adProfile'])
            ->whereIn('id', $this->selectedAdIds)
            ->where('is_active', true)
            ->get();

        $this->dailyCap = count($this->selectedAdIds);

        $this->watchedAdIds = AdView::query()
            ->where('user_id', auth()->id())
            ->whereIn('ad_id', $this->selectedAdIds)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->pluck('ad_id')
            ->all();
    }

    protected function selectionCacheKey(): string
    {
        return 'kadi.earn_coins.selection.'.auth()->id().'.'.today()->toDateString();
    }

    /**
     * Ads eligible to be offered to the current user right now:
     * active ad + active campaign within its schedule + budget remaining +
     * user hasn't hit that campaign's frequency_cap in the last 24h.
     */
    protected function eligibleAds(): Collection
    {
        $userId = auth()->id();

        $activeCampaigns = AdCampaign::query()
            ->where('status', CampaignStatus::Active)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->where('escrowed_budget', '>', 0)
            ->get(['id', 'frequency_cap']);

        if ($activeCampaigns->isEmpty()) {
            return collect();
        }

        $recentViewCounts = AdView::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDay())
            ->whereIn('ad_campaign_id', $activeCampaigns->pluck('id'))
            ->selectRaw('ad_campaign_id, COUNT(*) as cnt')
            ->groupBy('ad_campaign_id')
            ->pluck('cnt', 'ad_campaign_id');

        $eligibleCampaignIds = $activeCampaigns
            ->filter(fn ($c) => ($recentViewCounts[$c->id] ?? 0) < $c->frequency_cap)
            ->pluck('id');

        if ($eligibleCampaignIds->isEmpty()) {
            return collect();
        }

        return Ad::query()
            ->where('is_active', true)
            ->whereIn('ad_campaign_id', $eligibleCampaignIds)
            ->get();
    }

    /**
     * Writes one row to ad_analytic_events. device_platform/app_version/
     * country are left null — TODO: populate these from whatever client
     * info you actually capture (user agent parsing, a mobile app header,
     * a GeoIP lookup, etc.) if/when you track that.
     */
    protected function logEvent(string $type, int $adId, int $campaignId, ?int $adViewId = null, array $data = []): void
    {
        AdAnalyticEvent::create([
            'ad_id' => $adId,
            'ad_view_id' => $adViewId,
            'user_id' => auth()->id(),
            'ad_campaign_id' => $campaignId,
            'event_type' => $type,
            'event_data' => $data ?: null,
            'device_platform' => null,
            'app_version' => null,
            'country' => null,
            'occurred_at' => now(),
        ]);
    }

    // ── Step 1: pre-roll (reward message + thumbnail, per spec) ──────

    public function startWatch(int $adId): void
    {
        $this->earnError = null;

        if (! in_array($adId, $this->selectedAdIds, true)) {
            $this->earnError = "That ad isn't part of today's selection.";

            return;
        }

        if (in_array($adId, $this->watchedAdIds, true)) {
            $this->earnError = 'You already claimed this ad today.';

            return;
        }

        $ad = Ad::with('adCampaign')->find($adId);

        if (! $ad || ! $ad->is_active || ! $ad->adCampaign || $ad->adCampaign->status !== CampaignStatus::Active) {
            $this->earnError = 'This ad is no longer available.';

            return;
        }

        $view = AdView::create([
            'ad_id' => $ad->id,
            'ad_campaign_id' => $ad->ad_campaign_id,
            'user_id' => auth()->id(),
            'status' => 'requested',
        ]);

        $this->logEvent('requested', $ad->id, $ad->ad_campaign_id, $view->id);
        $this->logEvent('loaded', $ad->id, $ad->ad_campaign_id, $view->id);

        $this->activeAd = $ad;
        $this->activeViewId = $view->id;
        $this->playing = false;
        $this->viewCompleted = false;
        $this->rewardEarned = null;
    }

    // ── Step 2: user confirms → actual playback starts ────────────────

    public function beginPlayback(): void
    {
        if (! $this->activeAd || ! $this->activeViewId) {
            return;
        }

        AdView::where('id', $this->activeViewId)->update(['status' => 'started', 'started_at' => now()]);

        $this->logEvent('impression', $this->activeAd->id, $this->activeAd->ad_campaign_id, $this->activeViewId);
        $this->logEvent('video_started', $this->activeAd->id, $this->activeAd->ad_campaign_id, $this->activeViewId);

        $this->playing = true;
    }

    // ── Called from JS at 25/50/75% watched ────────────────────────────

    public function trackProgress(int $viewId, int $percentage): void
    {
        if (! $this->activeViewId || $this->activeViewId !== $viewId || ! $this->activeAd) {
            return;
        }

        $eventType = match ($percentage) {
            25 => 'watched_25',
            50 => 'watched_50',
            75 => 'watched_75',
            default => null,
        };

        if (! $eventType) {
            return;
        }

        $this->logEvent($eventType, $this->activeAd->id, $this->activeAd->ad_campaign_id, $viewId);

        AdView::where('id', $viewId)->update(['watched_percentage' => $percentage]);
    }

    // ── Called from JS on the video's 'ended' event ────────────────────

    public function completeView(int $viewId): void
    {
        if (! $this->activeViewId || $this->activeViewId !== $viewId) {
            return;
        }

        $view = AdView::where('user_id', auth()->id())->find($viewId);

        if (! $view) {
            return;
        }

        if ($view->status === 'completed') {
            // Duplicate 'ended' event (e.g. browser fired it twice) — the
            // charge/reward already happened, just show the success screen.
            $this->rewardEarned = $view->reward_amount;
            $this->viewCompleted = true;

            return;
        }

        $rewardAmount = 0;

        DB::transaction(function () use ($view, &$rewardAmount) {
            $ad = Ad::query()->lockForUpdate()->find($view->ad_id);
            $campaign = $ad ? AdCampaign::query()->lockForUpdate()->find($ad->ad_campaign_id) : null;

            if (! $ad || ! $campaign) {
                $view->update(['status' => 'error']);

                return;
            }

            if ($campaign->escrowed_budget < $ad->cost_per_view) {
                $view->update(['status' => 'incomplete', 'watched_percentage' => 100]);
                $this->logEvent('playback_error', $ad->id, $campaign->id, $view->id, ['reason' => 'campaign budget exhausted']);
                $this->earnError = 'This ad just ran out of budget — try another one.';

                return;
            }

            $wallet = AdWallet::query()->where('ad_profile_id', $campaign->ad_profile_id)->first();

            $campaign->escrowed_budget -= $ad->cost_per_view;
            $campaign->spent_budget += $ad->cost_per_view;

            if ($campaign->escrowed_budget <= 0) {
                $campaign->status = CampaignStatus::Exhausted;
            }

            $campaign->save();

            $transaction = AdWalletTransaction::create([
                'ad_wallet_id' => $wallet?->id,
                'type' => 'view_charge',
                'amount' => -$ad->cost_per_view,
                // balance_after tracks the CAMPAIGN's escrow here (what this
                // charge actually drew down) — not the advertiser's free
                // wallet balance, which doesn't move again until top-up or
                // campaign_release. See ad-platform-db-schema.md.
                'balance_after' => $campaign->escrowed_budget,
                'ad_campaign_id' => $campaign->id,
                'ad_view_id' => $view->id,
                'description' => "Completed view — {$ad->title}",
            ]);

            $view->update([
                'status' => 'completed',
                'watched_percentage' => 100,
                'completed_at' => now(),
                'charge_amount' => $ad->cost_per_view,
                'ad_wallet_transaction_id' => $transaction->id,
                'reward_granted' => $ad->reward_requires_completion,
                'reward_amount' => $ad->reward_amount,
            ]);

            $this->logEvent('completed', $ad->id, $campaign->id, $view->id);

            if ($view->reward_granted) {
                $this->logEvent('reward_granted', $ad->id, $campaign->id, $view->id, ['amount' => $ad->reward_amount]);
                $rewardAmount = $ad->reward_amount;

                /*
                |------------------------------------------------------------
                | Credit the user's game wallet
                |------------------------------------------------------------
                | TODO: call your game wallet API here, e.g.:
                |
                |   KadiApi::creditCoins(auth()->user()->linked_id, $ad->reward_amount);
                |
                | Then mark the credit as applied and refresh whatever cached
                | balance the rest of the app reads from, mirroring how
                | Wallet\Index::refreshCustomer() does it:
                |
                |   $view->update(['reward_credited_to_game_wallet' => true]);
                |   Cache::forget('kadi.customer.'.auth()->id());
                */
            }
        });

        if ($this->earnError) {
            $this->closeView();

            return;
        }

        $this->watchedAdIds[] = $view->ad_id;
        $this->rewardEarned = $rewardAmount;
        $this->viewCompleted = true;
        $this->dispatch('wallet-refreshed');
    }

    // ── CTA click, shown after the video completes ─────────────────────

    public function recordClick(): void
    {
        if (! $this->activeAd || ! $this->activeViewId || ! $this->activeAd->click_url) {
            return;
        }

        // One charged click per view — otherwise a user mashing the CTA
        // button could repeatedly drain the advertiser's budget for a
        // single watch.
        if (AdClick::where('ad_view_id', $this->activeViewId)->exists()) {
            $this->dispatch('open-click-url', url: $this->activeAd->click_url);

            return;
        }

        DB::transaction(function () {
            $ad = Ad::query()->lockForUpdate()->find($this->activeAd->id);
            $campaign = $ad ? AdCampaign::query()->lockForUpdate()->find($ad->ad_campaign_id) : null;

            if (! $ad || ! $campaign || $campaign->escrowed_budget < $ad->cost_per_click) {
                return;
            }

            $wallet = AdWallet::query()->where('ad_profile_id', $campaign->ad_profile_id)->first();

            $campaign->escrowed_budget -= $ad->cost_per_click;
            $campaign->spent_budget += $ad->cost_per_click;

            if ($campaign->escrowed_budget <= 0) {
                $campaign->status = CampaignStatus::Exhausted;
            }

            $campaign->save();

            $transaction = AdWalletTransaction::create([
                'ad_wallet_id' => $wallet?->id,
                'type' => 'click_charge',
                'amount' => -$ad->cost_per_click,
                'balance_after' => $campaign->escrowed_budget,
                'ad_campaign_id' => $campaign->id,
                'ad_view_id' => $this->activeViewId,
                'description' => "CTA click — {$ad->title}",
            ]);

            AdClick::create([
                'ad_view_id' => $this->activeViewId,
                'ad_id' => $ad->id,
                'ad_campaign_id' => $campaign->id,
                'user_id' => auth()->id(),
                'charge_amount' => $ad->cost_per_click,
                'ad_wallet_transaction_id' => $transaction->id,
                'clicked_at' => now(),
            ]);

            $this->logEvent('cta_clicked', $ad->id, $campaign->id, $this->activeViewId);
        });

        $this->dispatch('open-click-url', url: $this->activeAd->click_url);
    }

    // ── User exits before completion ───────────────────────────────────

    public function closeView(?int $watchedPercentage = null): void
    {
        if ($this->activeViewId && ! $this->viewCompleted) {
            $view = AdView::find($this->activeViewId);

            if ($view && $view->status !== 'completed') {
                $view->update([
                    'status' => 'incomplete',
                    'watched_percentage' => $watchedPercentage,
                ]);

                $this->logEvent('closed', $view->ad_id, $view->ad_campaign_id, $view->id);
            }
        }

        $this->activeAd = null;
        $this->activeViewId = null;
        $this->playing = false;
        $this->viewCompleted = false;
        $this->rewardEarned = null;
    }

    public function reportPlaybackError(int $viewId, string $message = ''): void
    {
        $view = AdView::find($viewId);

        if ($view && $view->status !== 'completed') {
            $view->update(['status' => 'error']);
            $this->logEvent('playback_error', $view->ad_id, $view->ad_campaign_id, $view->id, ['message' => $message]);
        }

        $this->earnError = 'Something went wrong playing that ad — try another one.';
        $this->closeView();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.coins.⚡earn-coins')->layout('layouts.app');
    }
}
