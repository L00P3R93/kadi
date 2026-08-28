<?php

namespace App\Livewire\Ads;

use App\Enums\CampaignStatus;
use App\Models\AdCampaign;
use App\Models\AdCategory;
use App\Models\AdProfile;
use Illuminate\Contracts\View\Factory;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Campaigns | Kadi Kings')]
class Campaigns extends Component
{
    public bool $showFormModal = false;

    public ?int $editingId = null;

    public ?int $ad_profile_id = null;

    public ?int $ad_category_id = null;

    public string $name = '';

    public string $status = '';

    public float $total_budget = 0.00;

    public int $frequency_cap = 5;

    public int $priority = 5;

    public string $starts_at = '';

    public string $ends_at = '';

    public ?string $successMessage = null;

    public function rules(): array
    {
        $rules = [
            'ad_profile_id' => ['required', 'exists:ad_profiles,id'],
            'ad_category_id' => ['required', 'exists:ad_categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string'],
            'total_budget' => ['required', 'numeric', 'min:0'],
            'frequency_cap' => ['required', 'integer', 'min:1'],
            'priority' => ['required', 'integer', 'min:1', 'max:10'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];

        // Status is only ever manually set when editing (approve/reject/pause
        // an existing campaign). On create it's computed in save() from the
        // selected category's requires_approval flag, so it's intentionally
        // left out of the create-time rules — including it there would mean
        // validating whatever $this->status happens to default to instead
        // of letting the category drive it.
        if ($this->editingId) {
            $rules['status'] = ['required', Rule::enum(CampaignStatus::class)];
        }

        return $rules;
    }

    public function openCreateModal(): void
    {
        $this->authorize('manage ad campaigns');
        $this->resetForm();
        $this->showFormModal = true;
    }

    #[On('campaign-edit')]
    public function openEditModal(int $id): void
    {
        $this->authorize('manage ad campaigns');

        $campaign = AdCampaign::query()->findOrFail($id);

        $this->editingId = $campaign->id;
        $this->ad_profile_id = $campaign->ad_profile_id;
        $this->ad_category_id = $campaign->ad_category_id;
        $this->name = $campaign->name;
        $this->status = $campaign->status->value;
        // datetime-local inputs need "Y-m-d\TH:i" — the raw Carbon cast
        // stringifies to "Y-m-d H:i:s" and silently fails to populate the input.
        $this->starts_at = $campaign->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->ends_at = $campaign->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->total_budget = (float) $campaign->total_budget;
        $this->frequency_cap = $campaign->frequency_cap;
        $this->priority = $campaign->priority;
        $this->successMessage = null;
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->authorize('manage ad campaigns');

        $validated = $this->validate();

        if (! $this->editingId) {
            $category = AdCategory::query()->find($validated['ad_category_id']);

            $validated['status'] = ($category && $category->requires_approval)
                ? CampaignStatus::PendingReview->value
                : CampaignStatus::Active->value;

            // Escrow starts equal to the funded budget — nothing has been
            // spent yet. See ad-platform-db-schema.md for the escrow model.
            $validated['escrowed_budget'] = $validated['total_budget'];
        }

        AdCampaign::query()->updateOrCreate(['id' => $this->editingId], $validated);

        $this->successMessage = $this->editingId ? 'Campaign updated.' : 'Campaign created.';
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
        $this->reset([
            'editingId', 'ad_profile_id', 'ad_category_id', 'name', 'status',
            'starts_at', 'ends_at', 'total_budget', 'frequency_cap', 'priority',
        ]);
        $this->frequency_cap = 5;
        $this->priority = 5;
        $this->resetErrorBag();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.ads.campaigns', [
            'adProfiles' => AdProfile::query()->orderBy('id')->get(),
            'adCategories' => AdCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => CampaignStatus::cases(),
        ])->layout('layouts.app');
    }
}
