<div>
    <section class="relative overflow-hidden bg-[#0a0a0a] min-h-[300px] flex items-center">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(245,197,66,0.08) 0%, transparent 70%);"></div>

        <div class="relative z-10 w-full max-w-5xl mx-auto px-6 py-14 md:py-20 text-center">
            <h1 class="font-cinzel font-black text-3xl md:text-5xl text-[#f5c542] leading-tight tracking-wide mb-3"
                style="text-shadow: 0 0 30px rgba(245,197,66,0.35);">
                KADI FAQ
            </h1>
            <p class="text-gray-400 text-sm md:text-base leading-relaxed max-w-xl mx-auto">
                Quick answers about how to play Kadi online. For the full details, see the
                <a href="{{ route('rules') }}" wire:navigate class="text-[#f5c542] underline hover:text-[#ffde74]">Kadi rules</a>
                and the
                <a href="{{ route('game-guide') }}" wire:navigate class="text-[#f5c542] underline hover:text-[#ffde74]">games and prizes guide</a>.
            </p>
        </div>
    </section>

    <section class="bg-[#0a0a0a] pb-16 md:pb-20">
        <div class="mx-auto max-w-3xl px-6 space-y-3">
            @foreach ($faqs as $faq)
                <details class="group glass-card p-5" @if ($loop->first) open @endif>
                    <summary class="cursor-pointer list-none font-cinzel text-sm font-bold text-[#f5c542] md:text-base">
                        {{ $faq['q'] }}
                    </summary>
                    <p class="mt-3 text-sm leading-relaxed text-gray-400">{{ $faq['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
            ], $faqs),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
</div>
