@auth
<div
    @if($needsLoad) wire:init="loadBalance" @endif
    x-data="{ cooldown: false }"
    class="flex items-center gap-1"
>
    {{-- Balance pill — links to wallet page --}}
    <a
        href="{{ route('wallet') }}"
        wire:navigate
        class="flex items-center gap-1.5 rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-3 py-1.5 transition hover:border-[#f5c542]/60 hover:bg-[#f5c542]/15"
    >
        <span class="text-sm leading-none select-none">💰</span>

        {{-- Spinner: hidden by default, shown while loading --}}
        <span
            class="hidden"
            wire:loading.class.remove="hidden"
            wire:target="loadBalance,refresh"
        >
            <svg class="h-3.5 w-3.5 animate-spin text-[#f5c542]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </span>

        {{-- Balance text: visible by default, hidden while loading --}}
        <span
            class="text-sm font-semibold text-[#f5c542]"
            style="font-family: 'Cinzel', serif;"
            wire:loading.class="hidden"
            wire:target="loadBalance,refresh"
        >
            @if ($hasError)
                <span class="text-[#f5f5f0]/40 font-normal tracking-normal text-xs">unavailable</span>
            @elseif ($balance !== null)
                {{ session('currency.code', 'KES') }} {{ number_format($balance, 2) }}
            @else
                <span class="text-[#f5f5f0]/30 font-normal">—</span>
            @endif
        </span>
    </a>

    {{-- Refresh icon button --}}
    <button
        wire:click="refresh"
        @click="if (!cooldown) { cooldown = true; setTimeout(() => cooldown = false, 10000) }"
        :disabled="cooldown"
        :title="cooldown ? 'Please wait…' : 'Refresh balance'"
        aria-label="Refresh wallet balance"
        class="flex items-center justify-center rounded-full text-[#f5f5f0]/40 transition-colors
               h-11 w-11 sm:h-7 sm:w-7"
        :class="cooldown
            ? 'opacity-40 cursor-not-allowed'
            : 'hover:text-[#f5c542] hover:bg-[#f5c542]/10 cursor-pointer'"
    >
        {{-- Single SVG: animate-spin class is added/removed by Livewire while refresh runs --}}
        <svg
            wire:loading.class="animate-spin"
            wire:target="refresh"
            class="h-3.5 w-3.5"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
        >
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
    </button>
</div>
@endauth
