@auth
    <div
        @if($needsLoad) wire:init="loadBalance" @endif
        x-data="{ cooldown: false }"
        class="flex items-center gap-2"
    >
        {{-- Balance pill — links to wallet page --}}
        <a
            href="{{ route('wallet') }}"
            wire:navigate
            class="relative flex h-10 items-center gap-2 rounded-full border border-yellow-800/30 px-4 transition hover:border-[#f5c542]/50"
            title="{{ __('Wallet balance') }}"
        >
            {{-- Spinner: hidden by default, shown while loading --}}
            <span
                class="hidden"
                wire:loading.class.remove="hidden"
                wire:target="loadBalance, refreshWallet"
            >
                <svg class="size-5 animate-spin text-[#f5c542]" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </span>

            {{-- Balance content: visible by default, hidden while loading --}}
            <span
                class="flex items-center gap-1.5"
                wire:loading.class="hidden"
                wire:target="loadBalance, refreshWallet"
            >
                <span class="text-sm leading-none select-none" aria-hidden="true">💰</span>

                <span class="text-sm font-semibold text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                    @if ($hasError)
                        <span class="text-xs font-normal tracking-normal text-[#f5f5f0]/40">{{ __('unavailable') }}</span>
                    @elseif ($balance !== null)
                        {{ number_format($balance) }}
                    @else
                        <span class="font-normal text-[#f5f5f0]/30">—</span>
                    @endif
                </span>
            </span>
        </a>

        {{-- Refresh — mirrors the notifications-bell button styling --}}
        <button
            type="button"
            x-on:click="
                if (cooldown) return;
                cooldown = true;
                setTimeout(() => cooldown = false, 10000);
                $wire.refreshWallet();
            "
            :disabled="cooldown"
            :title="cooldown ? '{{ __('Please wait…') }}' : '{{ __('Refresh balance') }}'"
            aria-label="{{ __('Refresh wallet balance') }}"
            class="relative flex h-10 w-10 items-center justify-center rounded-full border border-yellow-800/30 text-[#f5f5f0]/70 transition hover:border-[#f5c542]/50 hover:text-[#f5c542] disabled:cursor-not-allowed disabled:opacity-40"
            :class="cooldown && 'opacity-40 cursor-not-allowed'"
        >
            <svg
                wire:loading.class="animate-spin text-[#f5c542]"
                wire:target="refreshWallet"
                class="size-5"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
        </button>
    </div>
@endauth
