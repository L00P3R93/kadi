<div class="flex min-h-screen bg-[#0a0a0a]">
    {{-- Left: Sports Sidebar --}}
    <div class="w-56 hidden lg:flex flex-col border-r border-[#222]">
        <livewire:sportsbook.sports-sidebar />
    </div>

    {{-- Center: Event List --}}
    <div class="flex-1 overflow-y-auto">
        <livewire:sportsbook.event-list />
    </div>

    {{-- Right: Bet Slip --}}
    <div class="w-72 hidden lg:flex flex-col border-l border-[#222]">
        <livewire:sportsbook.bet-slip />
    </div>
</div>
