<?php

namespace App\Livewire\Storefront\Product;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

class Show extends Component
{
    public Product $product;

    public int $quantity = 1;

    public ?string $selectedVariantId = null;

    public function mount(Product $product): void
    {
        // Route model binding resolves regardless of status — a non-active
        // product still has a slug and still exists — so we gate visibility
        // here rather than in the route/binding itself.
        abort_unless($product->status === ProductStatus::ACTIVE, 404);

        $this->product = $product->load(['productCategory', 'variants' => fn ($q) => $q->where('is_active', true)]);

        if ($this->product->variants->isNotEmpty()) {
            $this->selectedVariantId = (string) $this->product->variants->first()->id;
        }
    }

    public function getSelectedVariantProperty()
    {
        if (! $this->selectedVariantId) {
            return null;
        }

        return $this->product->variants->firstWhere('id', (int) $this->selectedVariantId);
    }

    public function getRelatedProductsProperty()
    {
        return Product::query()
            ->storefront()
            ->where('product_category_id', $this->product->product_category_id)
            ->where('id', '!=', $this->product->id)
            ->limit(8)
            ->get();
    }

    public function addToCart(): void
    {
        // Wired to Cart/CartItem once checkout flow lands — placeholder for now.
        $this->dispatch('cart-updated');
    }

    #[Title('Product | Kadi Kings')]
    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('livewire.storefront.product.show')->layout('layouts.app');
    }
}
