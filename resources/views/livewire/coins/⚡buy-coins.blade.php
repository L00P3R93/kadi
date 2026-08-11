<div
    x-data="{
        tab: 'all',
        confirmOpen: false,
        selected: null,
        hasPhone: @js((bool) (auth()->user()->phone ?? false)),
        openOption(option) {
            if (! this.hasPhone) {
                $wire.dispatch('open-phone-required');
                return;
            }
            this.selected = option;
            this.confirmOpen = true;
        }
    }"
    x-on:wallet-refreshed.window="confirmOpen = false"
    class="min-h-screen bg-[#0a0a0a] pt-14 pb-20"
>
    {{-- Ambient background glow --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 h-[420px] -z-0"
         style="background: radial-gradient(60% 60% at 50% 0%, rgba(245,197,66,0.06) 0%, transparent 70%);"></div>

    <div class="relative mx-auto max-w-5xl px-6">

        {{-- ═══ Header ═══ --}}
        <div class="mb-10 text-center">
            <h1 class="font-cinzel text-3xl font-bold text-[#f5f5f0] md:text-4xl">Load Your Vault</h1>
            <p class="mx-auto mt-2 max-w-md text-sm text-[#6b6b6b]">
                Top up coins or grab a perk pack — tap a pack and confirm the STK push on your phone.
            </p>
        </div>

        {{-- ═══ Balance summary ═══ --}}
        <div class="glass-card mb-4 flex flex-col items-center justify-between gap-4 p-6 sm:flex-row">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full border-2 border-dashed border-[#f5c542]/40"
                     style="background: radial-gradient(circle at 50% 40%, rgba(245,197,66,0.22), rgba(245,197,66,0.04));">
                    <span class="text-2xl">💰</span>
                </div>
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Current Balance</div>
                    <div class="font-cinzel text-2xl font-black text-[#f5c542]">{{ number_format($balance ?? 0) }} Coins</div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-[#6b6b6b]">
                <div class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    Secure Checkout
                </div>
                <div class="h-3 w-px bg-gray-700"></div>
                <div class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    Credited Instantly
                </div>
            </div>
        </div>

        {{-- ═══ Earn Coins CTA — clear alternative to paying ═══ --}}
        <a href="{{ route('earn-coins') }}" wire:navigate
           class="group mb-8 flex flex-col items-center justify-between gap-3 rounded-xl border border-[#f5c542]/25 bg-[#f5c542]/[0.06] px-5 py-4 text-center transition hover:border-[#f5c542]/50 hover:bg-[#f5c542]/10 sm:flex-row sm:text-left">
            <div class="flex items-center gap-3">
                <span class="text-xl">🎬</span>
                <div>
                    <div class="font-cinzel text-sm font-bold text-[#f5f5f0]"> Free Coins</div>
                    <div class="text-xs text-[#6b6b6b]">Watch quick ads and earn coins for free.</div>
                </div>
            </div>
            <span class="btn-casino-primary inline-flex flex-shrink-0 items-center gap-2 rounded-full px-5 py-2 text-xs">
                Earn Coins Free
                <span class="transition-transform group-hover:translate-x-1">→</span>
            </span>
        </a>

        {{-- ═══ STK push loading banner ═══ --}}
        <div
            wire:loading
            wire:target="initiatePurchase"
            class="mb-6 flex items-center justify-center gap-2 rounded-lg border border-orange-700 bg-orange-900/30 p-4 text-xs text-orange-400"
        >
            <span class="inline-block animate-spin">⟳</span> Sending STK push to your phone...
        </div>

        {{-- ═══ Error banner ═══ --}}
        @if ($purchaseError)
            <div class="mb-6 rounded-lg border border-red-800 bg-red-950/40 p-4 text-center text-xs text-red-400">
                {{ $purchaseError }}
            </div>
        @endif

        {{-- ═══ Featured: Best Value spotlight ═══ --}}
        @php
            $bestIndex = collect($purchaseOptions)->search(fn ($o) => $o['best'] ?? false);
            $best = $bestIndex !== false ? $purchaseOptions[$bestIndex] : null;
        @endphp
        @if ($best)
            <button
                type="button"
                @click="openOption({ index: {{ $bestIndex }}, type: '{{ $best['type'] }}', price: {{ $best['price'] }}, coins: {{ $best['coins'] ?? 'null' }}, label: '{{ $best['label'] ?? '' }}' })"
                class="group relative mb-10 flex w-full flex-col items-center gap-4 overflow-hidden rounded-2xl border border-[#f5c542]/40 p-6 text-center transition-all duration-300 hover:border-[#f5c542] sm:flex-row sm:text-left"
                style="background: linear-gradient(135deg, rgba(245,197,66,0.10), rgba(10,10,10,0.4) 60%);"
            >
                <img src="{{ asset('casino/crown.png') }}" alt=""
                     class="pointer-events-none absolute -right-6 -top-6 w-32 opacity-20 transition-transform duration-500 group-hover:scale-110 sm:w-40" />

                <div class="relative z-10 flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border-2 border-[#f5c542] bg-black/40 shadow-[0_0_25px_rgba(245,197,66,0.35)]">
                    <span class="text-3xl">👑</span>
                </div>

                <div class="relative z-10 flex-1">
                    <span class="inline-flex items-center gap-1 rounded-full bg-[#f5c542] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide text-black">
                        Best Value
                    </span>
                    <div class="mt-2 font-cinzel text-2xl font-black text-[#f5c542]">
                        +{{ number_format($best['coins']) }} Coins
                    </div>
                    <div class="text-xs text-[#6b6b6b]">The strongest coins-per-shilling pack on the table.</div>
                </div>

                <div class="relative z-10 flex flex-shrink-0 items-center gap-3">
                    <div class="text-right">
                        <div class="text-[10px] uppercase tracking-widest text-[#6b6b6b]">Pay</div>
                        <div class="font-cinzel text-lg font-bold text-[#f5f5f0]">KSH {{ number_format($best['price']) }}</div>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f5c542] text-black transition-transform group-hover:translate-x-1">→</span>
                </div>
            </button>
        @endif

        {{-- ═══ Tabs ═══ --}}
        <div class="mb-6 flex items-center justify-center gap-2">
            <button type="button" @click="tab = 'all'"
                    :class="tab === 'all' ? 'bg-[#f5c542] text-black' : 'text-[#f5f5f0]/60 border border-[#f5c542]/20 hover:border-[#f5c542]/40'"
                    class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-wide transition">All</button>
            <button type="button" @click="tab = 'coins'"
                    :class="tab === 'coins' ? 'bg-[#f5c542] text-black' : 'text-[#f5f5f0]/60 border border-[#f5c542]/20 hover:border-[#f5c542]/40'"
                    class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-wide transition">Coins</button>
            <button type="button" @click="tab = 'perks'"
                    :class="tab === 'perks' ? 'bg-[#f5c542] text-black' : 'text-[#f5f5f0]/60 border border-[#f5c542]/20 hover:border-[#f5c542]/40'"
                    class="rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-wide transition">Perks</button>
        </div>

        {{-- ═══ Purchase grid ═══ --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
            @foreach ($purchaseOptions as $index => $option)
                @php $group = $option['type'] === 'coins' ? 'coins' : 'perks'; @endphp
                <div x-show="tab === 'all' || tab === '{{ $group }}'" x-cloak>
                    <button
                        type="button"
                        @click="openOption({ index: {{ $index }}, type: '{{ $option['type'] }}', price: {{ $option['price'] }}, coins: {{ $option['coins'] ?? 'null' }}, label: '{{ $option['label'] ?? '' }}' })"
                        class="purchase-tile purchase-tile--{{ $group === 'coins' ? 'coins' : 'perk' }} w-full"
                    >
                        @if ($option['best'] ?? false)
                            <span class="purchase-tile__ribbon">Best Value</span>
                        @endif

                        <span class="purchase-tile__shine"></span>

                        <span class="purchase-tile__icon-wrap">
                            <span class="purchase-tile__icon">
                                @if ($option['type'] === 'emoji')
                                    😊
                                @elseif ($option['type'] === 'gift')
                                    🎁
                                @else
                                    💰
                                @endif
                            </span>
                        </span>

                        <span class="purchase-tile__body">
                            @if ($option['type'] === 'emoji')
                                <span class="purchase-tile__amount">Emojis</span>
                                <span class="purchase-tile__meta">Pack</span>
                            @elseif ($option['type'] === 'gift')
                                <span class="purchase-tile__amount">Gifts</span>
                                <span class="purchase-tile__meta">Pack</span>
                            @else
                                <span class="purchase-tile__amount">+{{ number_format($option['coins']) }}</span>
                                <span class="purchase-tile__meta">Coins</span>
                            @endif
                        </span>

                        <span class="purchase-tile__footer">
                            <span class="purchase-tile__price">KSH {{ number_format($option['price']) }}</span>
                            <span class="purchase-tile__arrow">→</span>
                        </span>
                    </button>
                </div>
            @endforeach
        </div>

        {{-- ═══ Trust footer ═══ --}}
        <div class="mt-12 flex flex-col items-center gap-2 text-center">
            <div class="flex items-center gap-2 text-[11px] text-[#6b6b6b]">
                <span class="mpesa-dot"></span> Payments processed securely via M-Pesa STK push
            </div>
            <p class="text-[10px] text-gray-700">Coins are non-refundable and have no cash value outside Kadi Kings.</p>
        </div>
    </div>

    {{-- ═══ Purchase confirmation modal ═══ --}}
    <div
        x-show="confirmOpen"
        x-cloak
        x-transition.opacity
        @click.self="confirmOpen = false"
        @keydown.escape.window="confirmOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
    >
        <div
            x-show="confirmOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="glass-card relative w-full max-w-sm rounded-2xl border border-[#f5c542]/30 p-6"
        >
            <button type="button" @click="confirmOpen = false"
                    class="absolute right-4 top-4 text-[#6b6b6b] transition hover:text-[#f5c542]">✕</button>

            <template x-if="selected">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border-2 border-[#f5c542]/40 bg-black/40">
                        <span class="text-2xl"
                              x-text="selected.type === 'coins' ? '💰' : (selected.type === 'gift' ? '🎁' : '😊')"></span>
                    </div>

                    <h3 class="mb-4 font-cinzel text-lg font-bold text-[#f5f5f0]">Confirm Purchase</h3>

                    <div class="my-4 mb-3 rounded-xl border border-[#f5c542]/15 bg-black/30 p-4">
                        <div class="font-cinzel text-xl font-black text-[#f5c542]" x-text="selected.type === 'coins' ? ('+' + Number(selected.coins).toLocaleString() + ' Coins') : selected.label"></div>
                        <div class="mt-1 text-sm text-[#6b6b6b]">KSH <span x-text="Number(selected.price).toLocaleString()"></span></div>
                    </div>

                    <p class="mb-5 text-xs leading-relaxed text-[#6b6b6b]">
                        We'll send an M-Pesa STK push to your registered phone number. Enter your M-Pesa PIN on your phone to complete the payment.
                    </p>

                    {{-- Error banner shown inside the modal too, in case the purchase fails after confirming --}}
                    @if ($purchaseError)
                        <div class="mb-4 rounded-lg border border-red-800 bg-red-950/40 p-3 text-center text-xs text-red-400">
                            {{ $purchaseError }}
                        </div>
                    @endif

                    <div class="flex gap-3">
                        <button type="button" @click="confirmOpen = false"
                                wire:loading.attr="disabled"
                                wire:target="initiatePurchase"
                                class="flex-1 rounded-lg border border-[#f5c542]/20 py-2.5 text-xs font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40 disabled:cursor-not-allowed disabled:opacity-40">
                            Cancel
                        </button>
                        <button type="button"
                                wire:loading.attr="disabled"
                                wire:target="initiatePurchase"
                                @click="$wire.initiatePurchase(selected.index)"
                                class="btn-casino-primary flex flex-1 items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="initiatePurchase">Confirm &amp; Pay</span>
                            <span wire:loading wire:target="initiatePurchase" class="inline-flex items-center gap-1.5">
                                <span class="inline-block animate-spin">⟳</span> Sending...
                            </span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <livewire:phone-required />
</div>
