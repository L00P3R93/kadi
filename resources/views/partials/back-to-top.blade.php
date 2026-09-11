{{-- Floating "back to top" button. Appears once the reader has scrolled past the hero. --}}
<div
    x-data="{ show: false }"
    x-init="window.addEventListener('scroll', () => { show = window.scrollY > 700 }, { passive: true })"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    x-cloak
    class="fixed bottom-24 right-4 z-40 lg:bottom-6 lg:right-6"
>
    <button
        type="button"
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        aria-label="Back to top"
        class="flex h-11 w-11 items-center justify-center rounded-full border border-[#f5c542]/40 bg-black/80 text-[#f5c542] backdrop-blur-xl transition-all duration-200 hover:border-[#f5c542] hover:bg-[#f5c542]/10 hover:-translate-y-0.5"
        style="box-shadow: 0 8px 24px rgba(0,0,0,0.5);"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>
</div>
