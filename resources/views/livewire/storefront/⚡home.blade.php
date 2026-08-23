<div class="min-h-screen bg-[#0a0a0a] pb-20">

    <x-storefront.hero-carousel :slides="$heroSlides" />

    <div class="relative mx-auto max-w-7xl px-6">

        <x-storefront.category-nav :categories="$categories" />

        <x-storefront.product-section
            eyebrow="Hand-Picked"
            title="Featured"
            :products="$featured"
        />

        <x-storefront.product-section
            eyebrow="Player Favorites"
            title="Popular Right Now"
            :products="$popular"
        />

        <x-storefront.product-section
            eyebrow="Moving Fast"
            title="Trending"
            :products="$trending"
        />

        <x-storefront.product-section
            eyebrow="Just Landed"
            title="New Arrivals"
            :products="$newArrivals"
        />

        @if ($featured->isEmpty() && $popular->isEmpty() && $trending->isEmpty() && $newArrivals->isEmpty())
            <div class="glass-card flex flex-col items-center gap-3 rounded-2xl p-12 text-center">
                <span class="text-3xl">🛍️</span>
                <h3 class="font-cinzel text-lg font-bold text-[#f5f5f0]">No products yet</h3>
                <p class="max-w-sm text-sm text-[#6b6b6b]">
                    Once products are added and flagged as featured, popular, trending, or new, they'll show up here automatically.
                </p>
            </div>
        @endif

    </div>
</div>
