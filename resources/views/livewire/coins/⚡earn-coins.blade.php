<div
    x-data
    x-on:open-click-url.window="window.open($event.detail.url, '_blank')"
    class="min-h-screen bg-[#0a0a0a] pt-14 pb-20"
>
    {{-- Ambient background glow --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 h-[420px] -z-0"
         style="background: radial-gradient(60% 60% at 50% 0%, rgba(245,197,66,0.06) 0%, transparent 70%);"></div>

    <div class="relative mx-auto max-w-6xl px-6">

        {{-- ═══ Header ═══ --}}
        <div class="mb-10 text-center">
            <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-[#f5c542]/25 bg-[#f5c542]/10 px-3 py-1">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#f5c542]"></span>
                <span class="font-cinzel text-[10px] uppercase tracking-[0.2em] text-[#f5c542]">Watch &amp; Earn</span>
            </div>
            <h1 class="font-cinzel text-3xl font-bold text-[#f5f5f0] md:text-4xl">Earn Coins</h1>
            <p class="mx-auto mt-2 max-w-md text-sm text-[#6b6b6b]">
                Today's picks — watch each one fully to claim your coins.
            </p>
        </div>

        {{-- ═══ Buy Coins CTA — clear alternative to earning ═══ --}}
        <a href="{{ route('buy-coins') }}" wire:navigate
           class="group mb-8 flex flex-col items-center justify-between gap-3 rounded-xl border border-[#f5c542]/25 bg-[#f5c542]/[0.06] px-5 py-4 text-center transition hover:border-[#f5c542]/50 hover:bg-[#f5c542]/10 sm:flex-row sm:text-left">
            <div class="flex items-center gap-3">
                <span class="text-xl">🎬</span>
                <div>
                    <div class="font-cinzel text-sm font-bold text-[#f5f5f0]">Buy Coins</div>
                    <div class="text-xs text-[#6b6b6b]">Load Coins into your vault</div>
                </div>
            </div>
            <span class="btn-casino-primary inline-flex flex-shrink-0 items-center gap-2 rounded-full px-5 py-2 text-xs">
                Buy Coins
                <span class="transition-transform group-hover:translate-x-1">→</span>
            </span>
        </a>

        {{-- ═══ Daily progress ═══ --}}
        <div class="glass-card mb-8 p-6">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎬</span>
                    <span class="font-cinzel text-sm font-bold text-[#f5f5f0]">Today's Progress</span>
                </div>
                <span class="font-cinzel text-sm font-bold text-[#f5c542]">
                    {{ count($watchedAdIds) }} / {{ $dailyCap }} watched
                </span>
            </div>
            <div class="earn-progress-track">
                <div class="earn-progress-fill" style="width: {{ $dailyCap > 0 ? min(100, (count($watchedAdIds) / $dailyCap) * 100) : 0 }}%"></div>
            </div>
            <p class="mt-2 text-[11px] text-[#6b6b6b]">A fresh set of ads is picked for you every day at midnight.</p>
        </div>

        {{-- ═══ Error banner ═══ --}}
        @if ($earnError)
            <div class="mb-6 rounded-lg border border-red-800 bg-red-950/40 p-4 text-center text-xs text-red-400">
                {{ $earnError }}
            </div>
        @endif

        {{-- ═══ Ad grid ═══ --}}
        @if ($ads->isEmpty())
            <div class="glass-card px-6 py-16 text-center">
                <p class="text-sm text-[#6b6b6b]">No ads available right now — check back soon.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($ads as $ad)
                    @php $claimed = in_array($ad->id, $watchedAdIds, true); @endphp
                    <div class="ad-card" wire:key="earn-ad-{{ $ad->id }}">

                        @if ($claimed)
                            <div class="ad-card__claimed-overlay">
                                <div class="flex flex-col items-center gap-1 text-center">
                                    <span class="text-2xl">✅</span>
                                    <span class="font-cinzel text-xs font-bold uppercase tracking-wide text-[#f5c542]">Claimed Today</span>
                                </div>
                            </div>
                        @endif

                        <div class="ad-card__thumb-wrap">
                            @if ($ad->thumbnail_url)
                                <img src="{{ $ad->thumbnail_url }}" alt="{{ $ad->title }}" class="ad-card__thumb" loading="lazy" decoding="async" />
                            @else
                                <span class="text-3xl opacity-30">🎬</span>
                            @endif
                            <span class="ad-card__reward-pill">🪙 +{{ $ad->reward_amount }}</span>
                            <span class="ad-card__duration-pill">{{ $ad->duration_seconds }}s</span>
                        </div>

                        <div class="ad-card__body">
                            <div>
                                <div class="ad-card__sponsor">{{ $ad->adCampaign->adProfile->company_name ?? $ad->adCampaign->name }}</div>
                                <div class="ad-card__title">{{ $ad->title }}</div>
                            </div>

                            <button
                                type="button"
                                wire:click="startWatch({{ $ad->id }})"
                                @disabled($claimed)
                                class="btn-casino-primary mt-auto flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                {{ $claimed ? 'Claimed ✓' : 'Watch & Earn ▶' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-12 flex flex-col items-center gap-2 text-center">
            <p class="text-[10px] text-gray-700">
                Rewarded ads refresh daily. Coins are credited once each video finishes playing.
            </p>
        </div>
    </div>

    {{-- ═══ Watch modal ═══ --}}
    @if ($activeAd)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 px-4 backdrop-blur-sm">
            <div class="glass-card relative w-full max-w-lg rounded-2xl border border-[#f5c542]/30 p-6">

                @if (! $playing && ! $viewCompleted)
                    {{-- ═══ Pre-roll: reward message + thumbnail ═══ --}}
                    <div class="text-center">
                        <button type="button" wire:click="closeView" class="absolute right-4 top-4 text-[#6b6b6b] transition hover:text-[#f5c542]">✕</button>

                        <div class="ad-card__thumb-wrap mb-4 rounded-xl">
                            @if ($activeAd->thumbnail_url)
                                <img src="{{ $activeAd->thumbnail_url }}" alt="{{ $activeAd->title }}" class="ad-card__thumb" />
                            @else
                                <span class="text-4xl opacity-30">🎬</span>
                            @endif
                            <span class="ad-card__duration-pill">{{ $activeAd->duration_seconds }}s</span>
                        </div>

                        <div class="font-cinzel text-xs font-bold uppercase tracking-widest text-[#f5c542]">
                            {{ $activeAd->adCampaign->adProfile->company_name ?? $activeAd->adCampaign->name }}
                        </div>
                        <h3 class="mt-1 font-cinzel text-lg font-bold text-[#f5f5f0]">{{ $activeAd->title }}</h3>
                        <p class="mt-2 text-sm text-[#f5f5f0]/70">{{ $activeAd->reward_message }}</p>

                        <button type="button" wire:click="beginPlayback"
                                wire:loading.attr="disabled" wire:target="beginPlayback"
                                class="btn-casino-primary mt-5 flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-xs">
                            <span wire:loading.remove wire:target="beginPlayback">▶ Start Watching</span>
                            <span wire:loading wire:target="beginPlayback" class="inline-flex items-center gap-1.5">
                                <span class="inline-block animate-spin">⟳</span> Loading...
                            </span>
                        </button>
                    </div>

                @elseif ($playing && ! $viewCompleted)
                    {{-- ═══ Playing ═══ --}}
                    <div
                        x-data="{
                            viewId: {{ $activeViewId }},
                            milestonesSent: [],
                            maxTime: 0,
                            remaining: {{ $activeAd->duration_seconds }},
                            init() {
                                const video = this.$refs.videoEl;
                                if (!video) return;

                                video.addEventListener('timeupdate', () => {
                                    if (video.currentTime > this.maxTime) {
                                        this.maxTime = video.currentTime;
                                    } else if (video.currentTime > this.maxTime + 1) {
                                        // Blocks jumping ahead when skip isn't allowed.
                                        video.currentTime = this.maxTime;
                                    }

                                    this.remaining = Math.max(0, Math.ceil(video.duration - video.currentTime));

                                    const pct = video.duration ? Math.floor((video.currentTime / video.duration) * 100) : 0;
                                    [25, 50, 75].forEach(milestone => {
                                        if (pct >= milestone && !this.milestonesSent.includes(milestone)) {
                                            this.milestonesSent.push(milestone);
                                            $wire.trackProgress(this.viewId, milestone);
                                        }
                                    });
                                });

                                video.addEventListener('ended', () => $wire.completeView(this.viewId));
                                video.addEventListener('error', () => $wire.reportPlaybackError(this.viewId, 'Video failed to load'));

                                video.play().catch(() => {});
                            }
                        }"
                    >
                        <div class="relative aspect-video w-full overflow-hidden rounded-xl bg-black" wire:ignore>
                            <video
                                x-ref="videoEl"
                                src="{{ $activeAd->video_url }}"
                                playsinline
                                @if ($activeAd->skip_allowed) controls @endif
                                class="h-full w-full"
                            ></video>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="font-cinzel text-xs font-bold text-[#f5f5f0]">{{ $activeAd->title }}</span>
                            <span class="rounded-full border border-[#f5c542]/25 bg-[#f5c542]/10 px-2.5 py-0.5 font-cinzel text-xs font-bold text-[#f5c542]" x-text="remaining + 's left'"></span>
                        </div>

                        @if ($activeAd->skip_allowed)
                            <button type="button" wire:click="closeView" class="mt-3 w-full rounded-lg border border-[#f5c542]/20 py-2 text-xs font-semibold text-[#f5f5f0]/60 transition hover:border-[#f5c542]/40">
                                Skip
                            </button>
                        @endif
                    </div>

                @else
                    {{-- ═══ Reward / CTA ═══ --}}
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-2 border-[#f5c542] bg-black/40 shadow-[0_0_25px_rgba(245,197,66,0.35)]">
                            <span class="text-3xl">🪙</span>
                        </div>
                        <h3 class="font-cinzel text-lg font-bold text-[#f5f5f0]">You Earned {{ $rewardEarned }} Coins!</h3>
                        <p class="mt-1 text-xs text-[#6b6b6b]">Nice work — come back tomorrow for a fresh set of ads.</p>

                        @if ($activeAd->cta_text && $activeAd->click_url)
                            <button type="button" wire:click="recordClick"
                                    class="btn-casino-primary mt-5 flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-xs">
                                {{ $activeAd->cta_text }}
                                @if ($activeAd->cta_subtitle)
                                    <span class="opacity-70">— {{ $activeAd->cta_subtitle }}</span>
                                @endif
                            </button>
                        @endif

                        <button type="button" wire:click="closeView"
                                class="mt-3 w-full rounded-lg border border-[#f5c542]/20 py-2.5 text-xs font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40">
                            Done
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
