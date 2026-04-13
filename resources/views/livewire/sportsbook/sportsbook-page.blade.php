<div class="flex min-h-screen bg-[#0a0a0a]">

    {{-- Left: Sports Sidebar — sticky content, scrolls independently --}}
    <div class="hidden lg:block w-56 flex-shrink-0 border-r border-[#222]">
        <div class="sticky top-0 max-h-screen overflow-y-auto">
            <livewire:sportsbook.sports-sidebar />
        </div>
    </div>

    {{-- Center: Event List — natural scroll with page --}}
    <div class="flex-1 min-w-0">
        <livewire:sportsbook.event-list />

        {{-- M-Pesa card — mobile only (desktop version is in right column) --}}
        <div class="lg:hidden px-3 pb-24">
            @include('partials.mpesa-card')
        </div>
    </div>

    {{-- Right: Bet Slip + M-Pesa card — desktop only, natural height --}}
    <div class="hidden lg:block w-72 flex-shrink-0 border-l border-[#222]">
        <livewire:sportsbook.bet-slip />
        @include('partials.mpesa-card')
    </div>

</div>
