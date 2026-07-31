<?php

namespace App\Livewire\Coins;

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Earn Coins | Kadi Kings')]
class EarnCoins extends Component
{
    public array $ads = [];

    public array $watchedToday = [];

    public int $dailyCap = 10;

    public ?string $earnError = null;

    public ?int $watchingAdId = null;

    public function mount(): void
    {
        $this->ads = $this->availableAds();
        $this->watchedToday = Cache::get($this->cacheKey(), []);
    }

    /**
     * Placeholder catalogue until the real ad-network integration is wired
     * up. Swap this for a call to the ad network's "available rewarded
     * ads" endpoint, e.g. AdNetworkApi::getRewardedAds(auth()->id()),
     * keeping the same shape (id, sponsor, title, thumbnail, reward,
     * duration) so the view doesn't need to change.
     */
    protected function availableAds(): array
    {
        return [
            ['id' => 1, 'sponsor' => 'Safaricom',     'title' => 'Data Bundles Made Easy',   'thumbnail' => 'casino/slots.png',    'reward' => 5,  'duration' => 15],
            ['id' => 2, 'sponsor' => 'Betika Deals',   'title' => 'Weekend Jackpot Promo',    'thumbnail' => 'casino/roulette.png', 'reward' => 8,  'duration' => 20],
            ['id' => 3, 'sponsor' => 'KCB Bank',       'title' => 'Open a Goal Savings Acct', 'thumbnail' => 'casino/poker.png',    'reward' => 10, 'duration' => 30],
            ['id' => 4, 'sponsor' => 'Jumia Kenya',    'title' => 'Flash Sale This Week',     'thumbnail' => 'casino/dice.png',     'reward' => 5,  'duration' => 15],
            ['id' => 5, 'sponsor' => 'Angel Palace',   'title' => 'New Table Games Launch',   'thumbnail' => 'casino/king.png',     'reward' => 6,  'duration' => 15],
            ['id' => 6, 'sponsor' => 'Airtel Money',   'title' => 'Send Cash Instantly',      'thumbnail' => 'casino/cherry.png',   'reward' => 5,  'duration' => 15],
            ['id' => 7, 'sponsor' => 'Kadi Kings VIP', 'title' => 'Join the VIP Table',       'thumbnail' => 'casino/crown.png',    'reward' => 12, 'duration' => 30],
            ['id' => 8, 'sponsor' => 'Naivas',         'title' => 'Grocery Deals Today',      'thumbnail' => 'casino/diamond.png',  'reward' => 5,  'duration' => 15],
        ];
    }

    protected function cacheKey(): string
    {
        return 'kadi.ads_watched.'.auth()->id().'.'.today()->toDateString();
    }

    public function watchAd(int $adId): void
    {
        $this->earnError = null;

        if (in_array($adId, $this->watchedToday, true)) {
            $this->earnError = 'You already claimed this ad today.';

            return;
        }

        if (count($this->watchedToday) >= $this->dailyCap) {
            $this->earnError = 'Daily earn limit reached. Come back tomorrow!';

            return;
        }

        $ad = collect($this->ads)->firstWhere('id', $adId);

        if (! $ad) {
            $this->earnError = 'Invalid ad selection';

            return;
        }

        $this->watchingAdId = $adId;

        /*
        |----------------------------------------------------------------
        | Rewarded-ad placeholder
        |----------------------------------------------------------------
        | Suggested real flow once the ad network is available:
        |
        |   1. Front end plays the ad via the network's SDK, triggered
        |      from a JS listener on a dispatched 'play-rewarded-ad'
        |      browser event (pass $ad['id']).
        |   2. On the network's completion callback, call this method's
        |      counterpart (or a dedicated confirmWatch()) to credit
        |      coins server-side via KadiApi::creditCoins($user->linked_id,
        |      $ad['reward']), then refresh + cache the customer profile
        |      exactly as Wallet\Index::refreshCustomer() does.
        |
        | For now the claim is recorded immediately (per-day, per-user,
        | cached until midnight) so the UI flow can be reviewed end to
        | end before the ad network is wired in.
        */
        $this->watchedToday[] = $adId;
        Cache::put($this->cacheKey(), $this->watchedToday, now()->endOfDay());
        $this->dispatch('wallet-refreshed');

        $this->watchingAdId = null;
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.coins.⚡earn-coins')->layout('layouts.app');
    }
}
