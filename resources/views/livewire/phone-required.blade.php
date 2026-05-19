<div>
@if ($show)
<div
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background: rgba(0,0,0,0.88); backdrop-filter: blur(10px);"
    x-data
>
    <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-[#f5c542]/25 bg-[#111111] shadow-[0_0_80px_rgba(245,197,66,0.12)]">

        {{-- Top shimmer line --}}
        <div class="h-px w-full" style="background: linear-gradient(90deg, transparent 0%, #f5c542 50%, transparent 100%);"></div>

        {{-- Radial glow (purely decorative) --}}
        <div class="pointer-events-none absolute -top-24 left-1/2 h-48 w-48 -translate-x-1/2 rounded-full"
             style="background: radial-gradient(circle, rgba(245,197,66,0.1) 0%, transparent 70%);"></div>

        <div class="relative px-8 pb-8 pt-7">

            {{-- Avatar + badge --}}
            <div class="mb-6 flex justify-center">
                <div class="relative">
                    @php
                        $avatarSrc = auth()->user()->avatar
                            ?? ('https://www.gravatar.com/avatar/' . md5(strtolower(trim(auth()->user()->email))) . '?d=robohash&r=pg&s=80');
                    @endphp
                    <img src="{{ $avatarSrc }}" alt="{{ auth()->user()->name }}"
                         class="h-20 w-20 rounded-full object-cover ring-2 ring-[#f5c542]/40" />
                    <span class="absolute -bottom-1 -right-1 flex h-7 w-7 items-center justify-center rounded-full bg-[#f5c542] text-base shadow-lg">
                        📱
                    </span>
                </div>
            </div>

            {{-- Heading --}}
            <div class="mb-6 text-center">
                <h2 class="mb-1 text-2xl font-black text-white" style="font-family: 'Cinzel', serif;">
                    One Last Step
                </h2>
                <p class="text-sm text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">
                    Hey <span class="font-semibold text-[#f5c542]">{{ auth()->user()->name }}</span>,
                    add your phone number to activate your account.
                </p>
            </div>

            {{-- Why it matters --}}
            <div class="mb-6 rounded-xl border border-[#f5c542]/10 bg-[#f5c542]/5 px-4 py-3">
                <ul class="space-y-1.5 text-xs text-[#8a8a8a]" style="font-family: 'Outfit', sans-serif;">
                    <li class="flex items-center gap-2">
                        <span class="text-[#f5c542]">✦</span> Required to play games &amp; tournaments
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-[#f5c542]">✦</span> Used for M-Pesa deposits &amp; withdrawals
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-[#f5c542]">✦</span> Keeps your account secure
                    </li>
                </ul>
            </div>

            {{-- Phone input --}}
            <div class="mb-5">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-widest text-[#6b6b6b]"
                       style="font-family: 'Outfit', sans-serif;">
                    Phone Number
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-[#6b6b6b]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 6.75z"/>
                        </svg>
                    </span>
                    <input
                        wire:model="phone"
                        type="tel"
                        placeholder="+254 7XX XXX XXX"
                        autofocus
                        class="w-full rounded-xl border bg-[#1a1a1a] py-3 pl-10 pr-4 text-sm text-white placeholder-[#4a4a4a] transition focus:outline-none focus:ring-0
                               {{ $errors->has('phone') ? 'border-red-500/60 focus:border-red-500' : 'border-[#2a2a2a] focus:border-[#f5c542]/60' }}"
                        style="font-family: 'Outfit', sans-serif;"
                        wire:keydown.enter="save"
                    />
                </div>
                @error('phone')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-400" style="font-family: 'Outfit', sans-serif;">
                        <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- CTA --}}
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="btn-casino-primary w-full rounded-xl py-3.5 text-sm font-bold tracking-wider transition disabled:cursor-not-allowed disabled:opacity-50"
                style="font-family: 'Cinzel', serif;"
            >
                <span wire:loading.remove wire:target="save">🎮 Unlock & Play Now</span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Saving…
                </span>
            </button>

        </div>
    </div>
</div>
@endif
</div>
