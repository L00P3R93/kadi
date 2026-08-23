<?php

namespace App\Livewire\Storefront;

use App\Enums\MerchandisingCollectionType;
use App\Enums\ProductStatus;
use App\Models\MerchandisingCollection;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Promotions | Kadi Kings')]
class Home extends Component
{
    /**
     * Storefront catalog data changes rarely relative to how often the page
     * is viewed, so it's cached. Bump/shorten this once you have a feel for
     * how often products/categories actually change, and wire cache
     * invalidation into the Product/ProductCategory observers when that's
     * built (see checklist).
     */
    protected int $cacheMinutes = 10;

    public function render(): Factory|View
    {
        return view('livewire.storefront.⚡home', [
            'heroSlides' => $this->heroSlides(),
            'categories' => $this->categories(),
            'featured' => $this->productsByFlag('is_featured', 'featured'),
            'popular' => $this->productsByFlag('is_popular', 'popular'),
            'trending' => $this->productsByFlag('is_trending', 'trending'),
            'newArrivals' => $this->productsByFlag('is_new', 'new'),
        ])->layout('layouts.app');
    }

    /**
     * Placeholder hero content, per spec section 4 — structured so this
     * method's body is the only thing that changes once there's a real
     * campaign/banner source (e.g. a MerchandisingCollection of a "hero"
     * type, or a dedicated banners table). The blade never needs to change.
     *
     * CTAs all point at storefront.home for now since that's the only
     * storefront route that exists yet — update cta_route/cta_param once
     * the category and deals pages are built.
     */
    protected function heroSlides(): Collection
    {
        $slides = Cache::remember('storefront.hero-slides', now()->addMinutes(15), function () {
            $collection = MerchandisingCollection::query()
                ->where('type', MerchandisingCollectionType::HERO)
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->with(['products' => function ($query) {
                    $query->storefront()
                        ->orderBy('merchandising_collection_product.sort_order');
                }])
                ->orderBy('sort_order')
                ->first();

            if (! $collection) {
                return [];
            }

            return $collection->products->map(fn ($product) => [
                'id' => $product->id,
                'title' => $product->name,
                'subtitle' => $product->short_description,
                'image' => $product->card_image_url,
                'money_price' => $product->money_price,
                'coin_price' => $product->coin_price,
                'url' => route('storefront.product', $product),
            ])->toArray();
        });

        return collect($slides);
    }

    protected function categories(): Collection
    {
        $data = Cache::remember('storefront.categories.top', now()->addMinutes($this->cacheMinutes), function () {
            return ProductCategory::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->withCount('products')
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        });

        return ProductCategory::hydrate($data);
    }

    protected function productsByFlag(string $flag, string $cacheKey): Collection
    {
        $data = Cache::remember("storefront.products.$cacheKey", now()->addMinutes($this->cacheMinutes), function () use ($flag) {
            return Product::query()
                ->where('status', ProductStatus::ACTIVE)
                ->where(function ($query) {
                    $query->where('is_purchasable_with_money', true)
                        ->orWhere('is_redeemable_with_coins', true);
                })
                ->where($flag, true)
                ->with([
                    'productCategory:id,name,slug',
                ])
                ->latest()
                ->limit(12)
                ->get()
                ->toArray();
        });

        return Product::hydrate($data)->load([
            'productCategory:id,name,slug',
        ]);
    }
}
