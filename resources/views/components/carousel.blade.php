@props([
    'title'       => '',
    'subtitle'    => '',
    'viewAllLink' => '#',
    'viewAllText' => 'View All',
])

<div
    x-data="{
        scrollEl: null,
        canScrollLeft: false,
        canScrollRight: false,
        init() {
            this.scrollEl = this.$refs.scrollContainer;
            this.updateButtons();
            this.scrollEl.addEventListener('scroll', () => this.updateButtons(), { passive: true });
            window.addEventListener('resize', () => this.updateButtons(), { passive: true });
        },
        updateButtons() {
            const el = this.scrollEl;
            this.canScrollLeft = el.scrollLeft > 4;
            this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 4);
        },
        scrollLeft()  { this.scrollEl.scrollBy({ left: -280, behavior: 'smooth' }); },
        scrollRight() { this.scrollEl.scrollBy({ left:  280, behavior: 'smooth' }); }
    }"
    class="relative"
>
    {{-- Section header --}}
    <div class="flex items-center justify-between mb-4 px-4 md:px-6">
        <div>
            <h2 class="text-xl font-bold text-white">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-gray-500 text-xs mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{-- Desktop prev/next arrows --}}
            <div class="hidden md:flex gap-1.5">
                <button
                    x-show="canScrollLeft"
                    @click="scrollLeft()"
                    class="w-8 h-8 rounded-full border border-[#333] text-white flex items-center justify-center
                           hover:border-[#f5c542] hover:text-[#f5c542] transition-all duration-150"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button
                    x-show="canScrollRight"
                    @click="scrollRight()"
                    class="w-8 h-8 rounded-full border border-[#333] text-white flex items-center justify-center
                           hover:border-[#f5c542] hover:text-[#f5c542] transition-all duration-150"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            <a href="{{ $viewAllLink }}" class="text-[#f5c542] text-sm font-semibold hover:underline">
                {{ $viewAllText }} →
            </a>
        </div>
    </div>

    {{-- Left fade (desktop, only when scrolled right) --}}
    <div
        x-show="canScrollLeft"
        class="hidden md:block absolute left-0 top-12 bottom-0 w-8 z-10
               bg-gradient-to-r from-[#0a0a0a] to-transparent pointer-events-none"
    ></div>

    {{-- Right fade (desktop, only when more content to the right) --}}
    <div
        x-show="canScrollRight"
        class="hidden md:block absolute right-0 top-12 bottom-0 w-8 z-10
               bg-gradient-to-l from-[#0a0a0a] to-transparent pointer-events-none"
    ></div>

    {{-- Scroll container — always flex nowrap, never wraps --}}
    <div
        x-ref="scrollContainer"
        class="flex flex-nowrap overflow-x-auto pb-4 scrollbar-none"
        style="
            gap: 0.75rem;
            padding-left: 1rem;
            padding-right: 1rem;
            scroll-padding-left: 1rem;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
        "
    >
        {{ $slot }}
    </div>
</div>
