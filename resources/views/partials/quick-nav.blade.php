{{--
    Sticky, scroll-spy in-page navigation. Sits directly under the fixed top
    navbar and tracks which section is in view, so the reader always has a
    way back to any section without scrolling up.

    Usage:
        @include('partials.quick-nav', ['links' => [
            ['href' => '#objective', 'label' => 'Objective'],
            ...
        ]])
--}}
@php $links = $links ?? []; @endphp
<div
    x-data="{
        links: {{ \Illuminate\Support\Js::from($links) }},
        active: {{ \Illuminate\Support\Js::from($links[0]['href'] ?? null) }},
        io: null,
        init() {
            this.io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) this.active = '#' + entry.target.id;
                });
            }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
            this.links.forEach((link) => {
                const el = document.querySelector(link.href);
                if (el) this.io.observe(el);
            });
        }
    }"
    class="sticky top-[68px] z-40 border-b border-[#f5c542]/10 bg-[#0a0a0a]/90 backdrop-blur-xl"
>
    <div class="mx-auto max-w-5xl px-6">
        <div class="scrollbar-none flex items-center gap-2 overflow-x-auto py-3 md:justify-center">
            <template x-for="link in links" :key="link.href">
                <a :href="link.href"
                   @click="active = link.href"
                   :class="active === link.href
                        ? 'border-[#f5c542] bg-[#f5c542]/10 text-[#f5c542]'
                        : 'border-white/10 text-[#f5f5f0]/60 hover:border-[#f5c542]/40 hover:text-[#f5c542]'"
                   class="shrink-0 rounded-full border px-4 py-1.5 text-xs font-semibold whitespace-nowrap transition-colors duration-200"
                   x-text="link.label">
                </a>
            </template>
        </div>
    </div>
</div>
