@props(['product'])

@php
    $stockLeft = max(($product->stock_quantity ?? 0) - ($product->reserved_quantity ?? 0), 0);

    // Digital/reward products may not track stock the same way physical
    // merch does — this only treats a product as "out of stock" when it
    // actually requires shipping. Revisit once product_type semantics
    // (reward vs. merch) are confirmed.
    $outOfStock = $product->requires_shipping && $stockLeft <= 0;
    $lowStock = ! $outOfStock && $product->low_stock_threshold && $stockLeft > 0 && $stockLeft <= $product->low_stock_threshold;

    $hasMoneyDiscount = $product->original_money_price > 0 && $product->original_money_price > $product->money_price;
    $hasCoinDiscount = $product->original_coin_price > 0 && $product->original_coin_price > $product->coin_price;

    // Assumes ProductImage::path is a public-disk-relative path. Product
    // also implements HasMedia (Spatie) — pick ONE source of truth for
    // images before this ships (see checklist) and simplify this block.
    $primaryImagePath = $product->card_image_url;
@endphp

<a href="{{ \Illuminate\Support\Facades\Route::has('storefront.product') ? route('storefront.product', $product->slug) : '#' }}"
   wire:navigate
    {{ $attributes->merge(['class' => 'product-card group relative flex flex-col']) }}
>
    <div class="product-card__image-wrap relative aspect-square overflow-hidden">
        @if ($primaryImagePath)
            <img src="{{ $primaryImagePath }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center bg-white/[0.02] text-3xl">🎁</div>
        @endif

        <div class="pointer-events-none absolute inset-0 flex items-start justify-between p-2.5">
            <div class="flex flex-col gap-1.5">
                @if ($product->is_new)
                    <span class="product-card__badge product-card__badge--new">New</span>
                @endif
                @if ($hasMoneyDiscount || $hasCoinDiscount)
                    <span class="product-card__badge product-card__badge--sale">Sale</span>
                @endif
            </div>
        </div>

        @if ($outOfStock)
            <div class="absolute inset-0 flex items-center justify-center bg-black/70">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-[#f5f5f0]/80">Out of Stock</span>
            </div>
        @elseif ($lowStock)
            <div class="absolute bottom-2 left-2 rounded-full bg-black/60 px-2 py-0.5 text-[10px] text-orange-300">
                Only {{ $stockLeft }} left
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-1.5 p-3">
        @if ($product->productCategory)
            <span class="text-[10px] uppercase tracking-wide text-[#6b6b6b]">{{ $product->productCategory->name }}</span>
        @endif

        <h3 class="line-clamp-2 text-sm font-semibold text-[#f5f5f0]">{{ $product->name }}</h3>

        <div class="mt-auto flex flex-wrap items-center gap-x-2 gap-y-0.5 pt-1">
            @if ($product->is_purchasable_with_money)
                <span class="font-cinzel text-sm font-bold text-[#f5c542]">
                    KES {{ number_format($product->money_price) }}
                </span>
                @if ($hasMoneyDiscount)
                    <span class="text-[11px] text-[#6b6b6b] line-through">
                        KES {{ number_format($product->original_money_price) }}
                    </span>
                @endif
            @endif

            @if ($product->is_redeemable_with_coins)
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#f5f5f0]/70">
                    @if ($product->is_purchasable_with_money)<span class="text-[#6b6b6b]">·</span>@endif
                    💰 {{ number_format($product->coin_price) }}
                </span>
            @endif
        </div>
    </div>
</a>
