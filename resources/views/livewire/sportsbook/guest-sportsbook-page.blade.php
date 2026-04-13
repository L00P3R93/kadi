<div
    x-data="{
        sidenavOpen: false,
        betslipOpen: false
    }"
    class="pt-16"
>
    {{-- Mobile sidebar backdrop --}}
    <div
        x-show="sidenavOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidenavOpen = false"
        class="fixed inset-0 bg-black/60 z-40 lg:hidden"
    ></div>

    {{-- Mobile betslip backdrop --}}
    <div
        x-show="betslipOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="betslipOpen = false"
        class="fixed inset-0 bg-black/70 z-40 lg:hidden"
    ></div>

    {{-- Three-column layout — natural height, page scrolls --}}
    <div class="flex min-h-[calc(100vh-4rem)] bg-[#0a0a0a]">

        {{-- Left: Sports Sidebar — fixed drawer on mobile, sticky column on desktop --}}
        <div
            :class="sidenavOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed top-16 left-0 bottom-0 w-64 z-50 border-r border-[#222]
                   transition-transform duration-200 ease-out
                   lg:static lg:w-56 lg:z-auto lg:transition-none lg:top-auto lg:bottom-auto lg:block"
        >
            <div class="sticky top-0 max-h-screen overflow-y-auto">
                <livewire:sportsbook.guest-sports-sidebar />
            </div>
        </div>

        {{-- Center: Event List — natural scroll with page --}}
        <div class="flex-1 min-w-0">
            <livewire:sportsbook.guest-event-list />

            {{-- M-Pesa card — mobile only (desktop version is in right column) --}}
            <div class="lg:hidden px-3 pb-24">
                @include('partials.mpesa-card')
            </div>
        </div>

        {{-- Right: Guest Bet Slip + M-Pesa (desktop only, natural height) --}}
        <div class="hidden lg:block w-72 flex-shrink-0 border-l border-[#222]">
            <livewire:sportsbook.guest-bet-slip />
            @include('partials.mpesa-card')
        </div>

    </div>

    {{-- Cache status --}}
    @php $cachedService = app(\App\Services\CachedSportsbookService::class); @endphp
    @if($cachedService->getGeneratedAt())
        <div class="text-center py-2 text-[10px] text-gray-700">
            Odds updated {{ \Carbon\Carbon::parse($cachedService->getGeneratedAt())->setTimezone('Africa/Nairobi')->diffForHumans() }}
        </div>
    @endif

    {{-- Mobile: Floating Bet Slip button (bottom-right) --}}
    <button
        @click="betslipOpen = true"
        class="fixed bottom-6 right-5 z-30 lg:hidden bg-[#f5c542] text-black rounded-full w-14 h-14
               flex items-center justify-center shadow-2xl active:scale-95 transition-transform duration-150"
        aria-label="Open bet slip"
    >
        <div class="relative flex items-center justify-center">
            {{-- Ticket / bet slip icon --}}
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
            {{-- Counter badge --}}
            <span
                x-show="$store.betSlip.count() > 0"
                x-text="$store.betSlip.count()"
                x-cloak
                class="absolute -top-3 -right-3 bg-red-500 text-white text-[10px] font-bold
                       rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1
                       leading-none"
            ></span>
        </div>
    </button>

    {{-- Mobile: Bet Slip bottom sheet --}}
    <div
        x-show="betslipOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] lg:hidden flex flex-col
               rounded-t-2xl overflow-hidden border-t border-[#222] bg-[#111]"
        @click.stop
    >
        {{-- Drag handle + close --}}
        <div class="relative flex items-center justify-center px-4 pt-3 pb-2 flex-shrink-0">
            <div class="w-10 h-1 bg-[#333] rounded-full"></div>
            <button
                @click="betslipOpen = false"
                class="absolute right-4 text-gray-500 hover:text-white transition-colors"
                aria-label="Close bet slip"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto min-h-0">
            <livewire:sportsbook.guest-bet-slip />
        </div>
    </div>

</div>
