<?php

namespace App\Livewire\Ads;

use App\Models\Ad;
use App\Models\AdCampaign;
use Illuminate\Contracts\View\Factory;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class Adverts extends Component
{
    use WithFileUploads, WithPagination;

    public AdCampaign $campaign;

    public bool $showFormModal = false;

    public ?int $editingId = null;

    // ── Ad fields ──────────────────────────────────────────────────
    public string $title = '';

    public string $description = '';

    public string $reward_message = '';

    public int $reward_amount = 10;

    public string $reward_type = 'coins';

    public string $video_source = 'upload'; // upload | external

    public string $video_url = '';

    public $videoFile = null; // Livewire\Features\SupportFileUploads\TemporaryUploadedFile

    public string $thumbnail_source = 'upload'; // upload | external

    public string $thumbnail_url = '';

    public $thumbnailFile = null;

    public string $cta_text = '';

    public string $cta_subtitle = '';

    public string $click_url = '';

    public int $duration_seconds = 10;

    public string $orientation = 'portrait';

    public bool $skip_allowed = false;

    public bool $reward_requires_completion = true;

    public bool $is_active = true;

    public ?string $successMessage = null;

    /**
     * TODO: pull these two from your ad_pricing_tiers / ad_settings tables
     * if you've built those out — see the DB schema doc. Hardcoded here so
     * this component doesn't reference models that may not exist yet.
     */
    private const array BASE_VIEW_COST = [10 => 0.50, 20 => 1.00, 30 => 2.00];

    private const float CTA_CLICK_COST = 1.00;

    public function mount(AdCampaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'reward_message' => ['required', 'string', 'max:255'],
            'reward_amount' => ['required', 'integer', 'min:1'],
            'reward_type' => ['required', 'string', 'max:30'],

            'video_source' => ['required', Rule::in(['upload', 'external'])],
            'video_url' => [Rule::requiredIf($this->video_source === 'external'), 'nullable', 'url', 'max:500'],
            'videoFile' => [
                Rule::requiredIf($this->video_source === 'upload' && ! $this->editingId),
                'nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:51200', // 50MB
            ],

            'thumbnail_source' => ['required', Rule::in(['upload', 'external'])],
            'thumbnail_url' => [Rule::requiredIf($this->thumbnail_source === 'external'), 'nullable', 'url', 'max:500'],
            'thumbnailFile' => [
                Rule::requiredIf($this->thumbnail_source === 'upload' && ! $this->editingId),
                'nullable', 'image', 'max:5120', // 5MB
            ],

            'cta_text' => ['nullable', 'string', 'max:50'],
            'cta_subtitle' => ['nullable', 'string', 'max:100'],
            'click_url' => ['nullable', 'url', 'max:500'],

            'duration_seconds' => ['required', Rule::in([10, 20, 30])],
            'orientation' => ['required', Rule::in(['portrait', 'landscape'])],
            'skip_allowed' => ['boolean'],
            'reward_requires_completion' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function openCreateModal(): void
    {
        $this->authorize('manage ad campaigns');
        $this->resetForm();
        $this->showFormModal = true;
    }

    /**
     * Dispatched from the Edit button on an ad card via
     * wire:click="$dispatch('ad-edit', { id: ... })".
     */
    #[On('ad-edit')]
    public function openEditModal(int $id): void
    {
        $this->authorize('manage ad campaigns');

        // Scoped to this campaign — an id dispatched from this page's own
        // cards is always same-campaign, but this guards against a forged
        // event trying to pull in another campaign's ad.
        $ad = Ad::query()->where('ad_campaign_id', $this->campaign->id)->findOrFail($id);

        $this->editingId = $ad->id;
        $this->title = $ad->title;
        $this->description = (string) $ad->description;
        $this->reward_message = $ad->reward_message;
        $this->reward_amount = $ad->reward_amount;
        $this->reward_type = $ad->reward_type;
        $this->video_source = $ad->video_source;
        $this->video_url = (string) $ad->video_url;
        $this->thumbnail_source = $ad->thumbnail_source;
        $this->thumbnail_url = (string) $ad->thumbnail_url;
        $this->cta_text = (string) $ad->cta_text;
        $this->cta_subtitle = (string) $ad->cta_subtitle;
        $this->click_url = (string) $ad->click_url;
        $this->duration_seconds = $ad->duration_seconds;
        $this->orientation = $ad->orientation;
        $this->skip_allowed = $ad->skip_allowed;
        $this->reward_requires_completion = $ad->reward_requires_completion;
        $this->is_active = $ad->is_active;
        $this->videoFile = null;
        $this->thumbnailFile = null;
        $this->successMessage = null;
        $this->showFormModal = true;
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function save(): void
    {
        $this->authorize('manage ad campaigns');

        $validated = $this->validate();

        // These two aren't real Ad columns — don't mass-assign them.
        unset($validated['videoFile'], $validated['thumbnailFile']);

        // When the source is 'upload', video_url/thumbnail_url are set
        // below via Media Library (new file) or left untouched entirely
        // (editing, no new file selected) — never from this array, or an
        // edit with no new file would wipe out the existing URL with ''.
        if ($this->video_source === 'upload') {
            unset($validated['video_url']);
        }
        if ($this->thumbnail_source === 'upload') {
            unset($validated['thumbnail_url']);
        }

        $multiplier = (float) ($this->campaign->adCategory->pricing_multiplier ?? 1);

        $validated['ad_campaign_id'] = $this->campaign->id;
        $validated['cost_per_view'] = round(self::BASE_VIEW_COST[$this->duration_seconds] * $multiplier, 2);
        $validated['cost_per_click'] = self::CTA_CLICK_COST;

        $ad = Ad::query()->updateOrCreate(['id' => $this->editingId], $validated);

        if ($this->video_source === 'upload' && $this->videoFile) {
            $media = $ad->addMedia($this->videoFile->getRealPath())
                ->usingFileName($this->videoFile->getClientOriginalName())
                ->toMediaCollection('video');

            $ad->update([
                'video_url' => $media->getUrl(),
                'video_storage_path' => $media->getPath(),
            ]);
        }

        if ($this->thumbnail_source === 'upload' && $this->thumbnailFile) {
            $media = $ad->addMedia($this->thumbnailFile->getRealPath())
                ->usingFileName($this->thumbnailFile->getClientOriginalName())
                ->toMediaCollection('thumbnail');

            $ad->update([
                'thumbnail_url' => $media->getUrl(),
                'thumbnail_storage_path' => $media->getPath(),
            ]);
        }

        $this->successMessage = $this->editingId ? 'Ad updated.' : 'Ad created.';
        $this->showFormModal = false;
        $this->resetForm();
    }

    /**
     * Dispatched from the active toggle on an ad card via
     * wire:click="$dispatch('ad-toggle-active', { id: ... })".
     */
    #[On('ad-toggle-active')]
    public function toggleActive(int $id): void
    {
        $this->authorize('manage ad campaigns');

        $ad = Ad::query()->where('ad_campaign_id', $this->campaign->id)->findOrFail($id);
        $ad->update(['is_active' => ! $ad->is_active]);
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function getCostPerViewPreviewProperty(): float
    {
        $multiplier = (float) ($this->campaign->adCategory->pricing_multiplier ?? 1);

        return round(self::BASE_VIEW_COST[$this->duration_seconds] * $multiplier, 2);
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'description', 'reward_message', 'cta_text',
            'cta_subtitle', 'click_url', 'videoFile', 'thumbnailFile',
            'video_url', 'thumbnail_url', 'skip_allowed',
        ]);
        $this->reward_amount = 10;
        $this->reward_type = 'coins';
        $this->video_source = 'upload';
        $this->thumbnail_source = 'upload';
        $this->duration_seconds = 10;
        $this->orientation = 'portrait';
        $this->reward_requires_completion = true;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.ads.adverts', [
            'ads' => $this->campaign->ads()->latest()->paginate(9),
        ])->layout('layouts.app');
    }
}
