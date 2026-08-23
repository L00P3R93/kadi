@props(['slides' => []])

@if (count($slides))
    <section
        x-data="{
        active: 0,
        total: {{ count($slides) }},
        timer: null,
        start() { this.timer = setInterval(() => this.next(), 6000) },
        stop() { clearInterval(this.timer) },
        next() { this.active = (this.active + 1) % this.total },
        prev() { this.active = (this.active - 1 + this.total) % this.total },
        go(i) { this.active = i },
    }"
        x-init="start()"
        @mouseenter="stop()" @mouseleave="start()"
        class="relative overflow-hidden"
        style="height: min(72vh, 560px);"
    >
        @foreach ($slides as $i => $slide)
            <div
                x-show="active === {{ $i }}"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0 scale-105"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0"
                x-cloak
            >
                <img src="{{ $slide['image'] }}" alt=""
                     class="absolute inset-0 h-full w-full object-cover"
                     loading="{{ $i === 0 ? 'eager' : 'lazy' }}">

                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/60 to-[#0a0a0a]/10"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#0a0a0a]/85 via-[#0a0a0a]/20 to-transparent"></div>

                <div class="relative z-10 flex h-full max-w-7xl mx-auto items-end px-6 pb-16 sm:items-center sm:pb-0">
                    <div class="max-w-lg">
                        @if ($slide['badge'] ?? null)
                            <span class="stat-badge mb-4">{{ $slide['badge'] }}</span>
                        @endif
                        <h1 class="font-cinzel text-3xl font-black leading-tight text-[#f5f5f0] sm:text-5xl">
                            {{ $slide['title'] }}
                        </h1>
                        <p class="mt-3 max-w-md text-sm text-[#f5f5f0]/70 sm:text-base">
                            {{ $slide['subtitle'] }}
                        </p>
                        <a href="{{ $slide['url'] && \Illuminate\Support\Facades\Route::has($slide['url']) ? route($slide['url']) : '#' }}"
                           wire:navigate
                           class="btn-casino-primary mt-6 inline-flex items-center gap-2 rounded-full px-6 py-3 text-xs">
                            {{ $slide['title'] }}
                            <span>→</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Dots --}}
        <div class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2 sm:bottom-8">
            @foreach ($slides as $i => $slide)
                <button
                    type="button"
                    @click="go({{ $i }})"
                    :class="active === {{ $i }} ? 'w-6 bg-[#f5c542]' : 'w-2 bg-[#f5c542]/30'"
                    class="h-2 rounded-full transition-all duration-300"
                    aria-label="Go to slide {{ $i + 1 }}"
                ></button>
            @endforeach
        </div>

        {{-- Prev / Next — desktop only --}}
        <button type="button" @click="prev()" aria-label="Previous slide"
                class="absolute left-4 top-1/2 z-10 hidden -translate-y-1/2 items-center justify-center rounded-full border border-[#f5c542]/20 bg-black/40 p-2.5 text-[#f5f5f0]/70 backdrop-blur transition hover:border-[#f5c542]/50 hover:text-[#f5c542] sm:flex">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" @click="next()" aria-label="Next slide"
                class="absolute right-4 top-1/2 z-10 hidden -translate-y-1/2 items-center justify-center rounded-full border border-[#f5c542]/20 bg-black/40 p-2.5 text-[#f5f5f0]/70 backdrop-blur transition hover:border-[#f5c542]/50 hover:text-[#f5c542] sm:flex">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
    </section>
@endif
