<?php

namespace App\Livewire\Tables;

use App\Models\AdCategory;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoriesTable extends Component
{
    /**
     * Flip is_active on a category. Dispatched from the status-toggle
     * custom column (resources/views/components/table/category-status-toggle.blade.php)
     * via wire:click="$dispatch('category-toggle-active', { id: ... })".
     */
    #[On('category-toggle-active')]
    public function toggleActive(int $id): void
    {
        $this->authorize('manage ad categories');

        $category = AdCategory::query()->findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);

        $this->dispatch('reset-table');
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.tables.categories-table', [
            'model' => AdCategory::class,
            'columns' => [
                'name' => 'Category',
                'key' => 'Key',
                'pricing_multiplier' => 'Multiplier',
                'requires_approval' => 'Approval',
                'is_active' => 'Status',
                'actions' => 'Actions',
            ],
            'searchable' => ['name', 'key'],
            'unsortable' => ['actions'],
            'customColumns' => [
                'pricing_multiplier' => 'components.table.category-multiplier',
                'requires_approval' => 'components.table.category-approval-badge',
                'is_active' => 'components.table.category-status-toggle',
                'actions' => 'components.table.category-actions',
            ]
        ]);
    }
}
