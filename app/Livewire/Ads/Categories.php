<?php

namespace App\Livewire\Ads;

use App\Models\AdCategory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Ad Categories | Kadi Kings')]
class Categories extends Component
{
    public bool $showFormModal = false;
    public ?int $editingId = null;
    public string $key = '';
    public string $name = '';
    public string $description = '';
    public float $pricing_multiplier = 1.00;
    public bool $requires_approval = false;
    public bool $is_active = true;

    public ?string $successMessage = null;

    public function rules(): array
    {
        return [
            'key' => [
                'required',
                'alpha_dash',
                'max:50',
                Rule::unique('ad_categories', 'key')->ignore($this->editingId),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'pricing_multiplier' => ['required', 'numeric', 'min:0.1', 'max:10'],
            'requires_approval' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }


    public function openCreateModal(): void
    {
        $this->authorize('manage ad categories');
        $this->resetForm();
        $this->showFormModal = true;
    }

    /**
     * Dispatched from the "Edit" button in
     * resources/views/components/table/category-actions.blade.php
     * (rendered inside the CategoriesTable child component) via
     * wire:click="$dispatch('category-edit', { id: ... })".
     */
    #[On('category-edit')]
    public function openEditModal(int $id): void
    {
        $this->authorize('manage ad categories');

        $category = AdCategory::query()->findOrFail($id);

        $this->editingId = $category->id;
        $this->key = $category->key;
        $this->name = $category->name;
        $this->description = (string) $category->description;
        $this->pricing_multiplier = (string) $category->pricing_multiplier;
        $this->requires_approval = $category->requires_approval;
        $this->is_active = $category->is_active;
        $this->successMessage = null;
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->authorize('manage ad categories');

        $validated = $this->validate();

        AdCategory::query()->updateOrCreate(
            ['id' => $this->editingId],
            $validated
        );

        $this->successMessage = $this->editingId ? 'Category updated.' : 'Category created.';
        $this->showFormModal = false;
        $this->resetForm();

        // The datatable is a separate child component (CategoriesTable) with
        // its own query — tell it to re-fetch so the new/edited row shows up
        // without a full page reload.
        $this->dispatch('reset-table');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'key', 'name', 'description', 'requires_approval']);
        $this->pricing_multiplier = '1.00';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('livewire.ads.categories')->layout('layouts.app');
    }
}
