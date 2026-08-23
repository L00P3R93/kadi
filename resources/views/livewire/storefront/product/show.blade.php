<div class="min-h-screen bg-[#0a0a0a] pt-10 pb-20">
    <div class="mx-auto max-w-6xl px-6">
        <nav class="mb-6 text-xs text-[#6b6b6b]">
            <a href="{{ route('storefront.home') }}" wire:navigate class="hover:text-[#f5c542]">Store</a>
            <span class="mx-1">/</span>
            <a href="{{ route('storefront.category', $product->productCategory) }}" wire:navigate class="hover:text-[#f5c542]">{{ $product->productCategory->name }}</a>
            <span class="mx-1">/</span>
            <span class="text-[#f5f5f0]">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-10 md:grid-cols-2">
            <div class="glass-card overflow-hidden rounded-2xl">
                <img src="{{ $product->card_image_url }}" alt="{{ $product->name }}" class="w-full object-cover" />
            </div>

            <div>
                <h1 class="font-cinzel text-2xl font-bold text-[#f5f5f0]">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-[#6b6b6b]">{{ $product->short_description }}</p>

                <div class="mt-6 flex items-baseline gap-3">
                    <span class="font-cinzel text-2xl font-black text-[#f5c542]">KSH {{ number_format($product->money_price) }}</span>
                    @if ($product->is_redeemable_with_coins)
                        <span class="text-xs text-[#6b6b6b]">or {{ number_format($product->coin_price) }} coins</span>
                    @endif
                </div>

                <button type="button" wire:click="addToCart" class="btn-casino-primary mt-6 rounded-full px-8 py-3 text-sm">
                    Add to Cart
                </button>
            </div>
        </div>

        @if ($this->relatedProducts->isNotEmpty())
            <div class="mt-16">
                <h2 class="font-cinzel mb-4 text-lg font-bold text-[#f5f5f0]">You Might Also Like</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($this->relatedProducts as $related)
                        <x-storefront.product-card :product="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
