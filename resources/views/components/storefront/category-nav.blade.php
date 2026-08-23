@props(['categories' => []])

@if (count($categories))
    <div class="mb-10 mt-8">
        <h2 class="mb-4 font-cinzel text-lg font-bold text-[#f5f5f0]">Shop by Category</h2>

        <div class="scrollbar-none flex gap-3 overflow-x-auto pb-2">
            @foreach ($categories as $category)
                <a href="{{ \Illuminate\Support\Facades\Route::has('storefront.category') ? route('storefront.category', $category->slug) : '#' }}"
                   wire:navigate
                   class="category-chip group"
                >
                    <span class="category-chip__icon">🛍️</span>
                    <span class="category-chip__label">{{ $category->name }}</span>
                    <span class="category-chip__count">{{ $category->products_count ?? 0 }} items</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
