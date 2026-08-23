<div class="min-h-screen bg-[#0a0a0a] pt-10 pb-20">
    <div class="mx-auto max-w-6xl px-6">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="font-cinzel text-2xl font-bold text-[#f5f5f0]">{{ $category->name }}</h1>

            <select wire:model.live="sortBy" class="rounded-lg border border-[#f5c542]/20 bg-black/40 px-3 py-1.5 text-xs text-[#f5f5f0]">
                <option value="newest">Newest</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4" wire:loading.class="opacity-50">
            @forelse ($products as $product)
                <x-storefront.product-card :product="$product" />
            @empty
                <p class="col-span-full text-center text-sm text-[#6b6b6b]">No products in this category yet.</p>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>
    </div>
</div>
