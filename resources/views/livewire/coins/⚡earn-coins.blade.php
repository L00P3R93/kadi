<div class="min-h-screen bg-[#0a0a0a] pt-14 pb-20">
    {{-- Ambient background glow --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 h-[420px] -z-0"
         style="background: radial-gradient(60% 60% at 50% 0%, rgba(245,197,66,0.06) 0%, transparent 70%);"></div>

    <div class="relative mx-auto max-w-6xl px-6">

        {{-- ═══ Header ═══ --}}
        <div class="mb-10 text-center">
            <h1 class="font-cinzel text-3xl font-bold text-[#f5f5f0] md:text-4xl">Earn Coins</h1>
            <p class="mx-auto mt-2 max-w-md text-sm text-[#6b6b6b]">
                Watch a short sponsor spot, claim your coins. No cash needed.
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
                    {{ count($watchedToday) }} / {{ $dailyCap }} watched
                </span>
            </div>
            <div class="earn-progress-track">
                <div class="earn-progress-fill" style="width: {{ $dailyCap > 0 ? min(100, (count($watchedToday) / $dailyCap) * 100) : 0 }}%"></div>
            </div>
            <p class="mt-2 text-[11px] text-[#6b6b6b]">Resets daily at midnight. Each sponsor spot can only be claimed once per day.</p>
        </div>

        {{-- ═══ Error banner ═══ --}}
        @if ($earnError)
            <div class="mb-6 rounded-lg border border-red-800 bg-red-950/40 p-4 text-center text-xs text-red-400">
                {{ $earnError }}
            </div>
        @endif

        {{-- ═══ Ad grid ═══ --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($ads as $ad)
                @php $claimed = in_array($ad['id'], $watchedToday, true); @endphp
                <div class="ad-card" wire:key="ad-{{ $ad['id'] }}">

                    @if ($claimed)
                        <div class="ad-card__claimed-overlay">
                            <div class="flex flex-col items-center gap-1 text-center">
                                <span class="text-2xl">✅</span>
                                <span class="font-cinzel text-xs font-bold uppercase tracking-wide text-[#f5c542]">Claimed Today</span>
                            </div>
                        </div>
                    @endif

                    <div class="ad-card__thumb-wrap">
                        <img src="{{ asset($ad['thumbnail']) }}" alt="{{ $ad['title'] }}" class="ad-card__thumb"
                             width="120" height="120" loading="lazy" decoding="async" />
                        <span class="ad-card__reward-pill">🪙 +{{ $ad['reward'] }}</span>
                        <span class="ad-card__duration-pill">{{ $ad['duration'] }}s</span>
                    </div>

                    <div class="ad-card__body">
                        <div>
                            <div class="ad-card__sponsor">{{ $ad['sponsor'] }}</div>
                            <div class="ad-card__title">{{ $ad['title'] }}</div>
                        </div>

                        <button
                            type="button"
                            wire:click="watchAd({{ $ad['id'] }})"
                            wire:loading.attr="disabled"
                            wire:target="watchAd({{ $ad['id'] }})"
                            @disabled($claimed)
                            class="btn-casino-primary mt-auto flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <span wire:loading.remove wire:target="watchAd({{ $ad['id'] }})">
                                {{ $claimed ? 'Claimed ✓' : 'Watch & Earn ▶' }}
                            </span>
                            <span wire:loading wire:target="watchAd({{ $ad['id'] }})" class="inline-flex items-center gap-1.5">
                                <span class="inline-block animate-spin">⟳</span> Loading...
                            </span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ═══ Trust footer ═══ --}}
        <div class="mt-12 flex flex-col items-center gap-2 text-center">
            <p class="text-[10px] text-gray-700">
                Rewarded ads refresh daily. Coins are credited instantly after each completed watch.
            </p>
        </div>
    </div>
</div>
