@props(['title', 'eyebrow' => null, 'products' => []])

@if (count($products))
    <section class="mb-12">
        <div class="mb-4 flex items-end justify-between">
            <div>
                @if ($eyebrow)
                    <div class="text-[10px] font-semibold uppercase tracking-widest text-[#f5c542]">{{ $eyebrow }}</div>
                @endif
                <h2 class="font-cinzel text-xl font-bold text-[#f5f5f0]">{{ $title }}</h2>
            </div>
            {{-- TODO: point at a real listing route once the product grid/filter
                 page exists (e.g. storefront.home with a ?sort= or ?section=
                 query, or a dedicated route per section). Left as '#' so it
                 doesn't silently 404. --}}
            <a href="#" class="text-xs font-semibold text-[#f5c542]/80 transition hover:text-[#f5c542]">
                View All →
            </a>
        </div>

        <div class="scrollbar-none -mx-6 flex gap-4 overflow-x-auto px-6 pb-2 sm:mx-0 sm:grid sm:grid-cols-3 sm:overflow-visible sm:px-0 lg:grid-cols-4">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" class="w-[168px] flex-shrink-0 sm:w-auto" />
            @endforeach
        </div>
    </section>
@endif
