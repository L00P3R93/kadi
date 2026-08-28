<?php

namespace App\Livewire\Ads;

use App\Models\AdPricingTier;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pricing Tiers | Kadi Kings')]
class PricingTiers extends Component
{
    public bool $showFormModal = false;

    public ?int $editingId = null;

    public int $duration_seconds = 0;

    public float $base_cost = 0.00;

    public ?string $successMessage = null;

    public function rules(): array
    {
        return [
            'duration_seconds' => ['required', 'numeric', 'min:60', 'max:86400'],
            'base_cost' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
        ];
    }

    public function openCreateModal(): void
    {
        $this->authorize('manage ad categories');
        $this->resetForm();
        $this->showFormModal = true;
    }

    #[On('pricing-tier-edit')]
    public function openEditModal(int $id): void
    {
        $this->authorize('manage ad categories');
        $pricingTier = AdPricingTier::query()->findOrFail($id);

        $this->editingId = $pricingTier->id;
        $this->duration_seconds = $pricingTier->duration_seconds;
        $this->base_cost = $pricingTier->base_cost;
        $this->successMessage = null;
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->authorize('manage ad categories');
        $validated = $this->validate();

        AdPricingTier::query()->updateOrCreate(['id' => $this->editingId], $validated);

        $this->successMessage = $this->editingId ? 'Pricing tier updated.' : 'Pricing tier created.';
        $this->showFormModal = false;
        $this->resetForm();

        $this->dispatch('reset-table');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'duration_seconds', 'base_cost']);
        $this->resetErrorBag();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.ads.pricing-tiers')->layout('layouts.app');
    }
}
