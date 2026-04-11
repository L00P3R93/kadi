<div class="mx-auto px-3 pt-16 pb-16">
    <div class="flex flex-col min-h-screen bg-[#0a0a0a]">
        {{-- Top promo banner --}}
        <!--<div class="bg-gradient-to-r from-[#1a1200] via-[#2a1f00] to-[#1a1200] border-b border-[#f5c542]/20 px-6 py-3 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🏆</span>
            <div>
                <div class="text-[#f5c542] font-bold text-sm">KADI KINGS SPORTSBOOK</div>
                <div class="text-gray-400 text-xs">Browse live odds — Sign in to place bets</div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('login') }}" wire:navigate
               class="bg-transparent border border-[#f5c542] text-[#f5c542] text-xs font-bold px-4 py-2 rounded hover:bg-[#f5c542] hover:text-black transition">
                Sign In
            </a>
            <a href="{{ route('register') }}" wire:navigate
               class="bg-[#f5c542] text-black text-xs font-bold px-4 py-2 rounded hover:bg-[#ffde74] transition">
                Join Now
            </a>
        </div>
    </div>-->

        {{-- Three-column layout --}}
        <div class="flex flex-1 overflow-hidden">
            {{-- Left: Sports Sidebar --}}
            <div class="w-56 hidden lg:flex flex-col border-r border-[#222]">
                <livewire:sportsbook.sports-sidebar />
            </div>

            {{-- Center: Event List --}}
            <div class="flex-1 overflow-y-auto">
                <livewire:sportsbook.guest-event-list />
            </div>

            {{-- Right: Guest Bet Slip --}}
            <div class="w-72 hidden lg:flex flex-col border-l border-[#222]">
                <livewire:sportsbook.guest-bet-slip />
            </div>
        </div>
    </div>
</div>
