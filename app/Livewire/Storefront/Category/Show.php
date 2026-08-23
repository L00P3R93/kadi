<?php

namespace App\Livewire\Storefront\Category;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public ProductCategory $category;

    #[Url(as: 'sort', history: true)]
    public string $sortBy = 'newest';

    public function mount(ProductCategory $category): void
    {
        abort_unless($category->is_active, 404);

        $this->category = $category;
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->storefront()
            ->where('product_category_id', $this->category->id)
            ->when($this->sortBy === 'price_low', fn ($q) => $q->orderBy('money_price'))
            ->when($this->sortBy === 'price_high', fn ($q) => $q->orderByDesc('money_price'))
            ->when($this->sortBy === 'newest', fn ($q) => $q->latest())
            ->paginate(24);
    }

    #[Title('Category | Kadi Kings')]
    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('livewire.storefront.category.show', [
            'products' => $this->products,
        ])->layout('layouts.app');
    }
}
