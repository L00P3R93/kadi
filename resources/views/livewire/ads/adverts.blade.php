<div>

    {{-- ═══ Section header ═══ --}}
    <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            @php($adsCount = $campaign->ads()->count())
            <h2 class="font-cinzel text-xl font-bold text-[#f5f5f0]">Ad Creatives</h2>
            <p class="mt-1 text-sm text-[#6b6b6b]">
                {{ $adsCount }} {{ $adsCount === 1 ? 'ad' : 'ads' }} in this campaign
            </p>
        </div>

        <button type="button" wire:click="openCreateModal" class="btn-casino-primary flex items-center gap-2 rounded-full px-5 py-2.5 text-xs whitespace-nowrap">
            <span class="text-base leading-none">+</span> New Ad
        </button>
    </div>

    {{-- ═══ Success banner ═══ --}}
    @if ($successMessage)
        <div class="mb-6 flex items-center gap-2 rounded-lg border border-green-800 bg-green-950/30 p-4 text-xs text-green-400">
            <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            {{ $successMessage }}
        </div>
    @endif

    {{-- ═══ Ad cards ═══ --}}
    @if ($ads->isEmpty())
        <div class="glass-card px-6 py-16 text-center">
            <p class="text-sm text-[#6b6b6b]">No ad creatives yet — add your first one to start serving this campaign.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($ads as $ad)
                <div class="ad-card" wire:key="ad-{{ $ad->id }}">

                    @unless ($ad->is_active)
                        <div class="absolute right-3 top-3 z-10 rounded-full border border-gray-700 bg-black/70 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                            Inactive
                        </div>
                    @endunless

                    <div class="ad-card__thumb-wrap">
                        @if ($ad->thumbnail_url)
                            <img src="{{ $ad->thumbnail_url }}" alt="{{ $ad->title }}" class="ad-card__thumb" loading="lazy" decoding="async" />
                        @else
                            <span class="text-3xl opacity-30">🎬</span>
                        @endif
                        <span class="ad-card__reward-pill">🪙 +{{ $ad->reward_amount }}</span>
                        <span class="ad-card__duration-pill">{{ $ad->duration_seconds }}s &middot; {{ ucfirst($ad->orientation) }}</span>
                    </div>

                    <div class="ad-card__body">
                        <div>
                            <div class="ad-card__sponsor">KES {{ number_format($ad->cost_per_view, 2) }} / view</div>
                            <div class="ad-card__title">{{ $ad->title }}</div>
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-2">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" wire:click="$dispatch('ad-toggle-active', { id: {{ $ad->id }} })" @checked($ad->is_active) class="peer sr-only">
                                <div class="h-5 w-9 rounded-full bg-gray-700 transition-colors peer-checked:bg-[#f5c542]"></div>
                                <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
                            </label>

                            <button type="button" wire:click="$dispatch('ad-edit', { id: {{ $ad->id }} })"
                                    class="rounded-lg border border-[#f5c542]/20 px-3 py-1.5 text-[11px] font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/50 hover:text-[#f5c542]">
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ═══ Pagination ═══ --}}
        @if ($ads->hasPages())
            <div class="mt-6 flex items-center justify-center gap-1">
                <button type="button" wire:click="previousPage" @disabled($ads->onFirstPage())
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#f5c542]/15 text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40 hover:text-[#f5c542] disabled:cursor-not-allowed disabled:opacity-30">
                    ‹
                </button>
                <span class="px-3 text-xs text-[#6b6b6b]">Page {{ $ads->currentPage() }} of {{ $ads->lastPage() }}</span>
                <button type="button" wire:click="nextPage" @disabled($ads->onLastPage())
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#f5c542]/15 text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40 hover:text-[#f5c542] disabled:cursor-not-allowed disabled:opacity-30">
                    ›
                </button>
            </div>
        @endif
    @endif

    {{-- ═══ Create / Edit modal ═══ --}}
    @if ($showFormModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.closeModal()"
            wire:click.self="closeModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-3 py-6 sm:px-4 sm:py-8 backdrop-blur-sm"
        >
            <div class="glass-card relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-[#f5c542]/30">

                {{-- Header — fixed, never scrolls --}}
                <div class="flex flex-shrink-0 items-center justify-between gap-4 border-b border-[#f5c542]/10 px-4 py-4 sm:px-6">
                    <h3 class="font-cinzel text-lg font-bold text-[#f5f5f0]">
                        {{ $editingId ? 'Edit Ad' : 'New Ad' }}
                    </h3>
                    <button type="button" wire:click="closeModal"
                            class="flex-shrink-0 text-[#6b6b6b] transition hover:text-[#f5c542]">✕</button>
                </div>

                {{-- Body — the only part that scrolls --}}
                <form wire:submit="save" id="ad-form" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-4 py-4 sm:px-6">

                    {{-- Title --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Title</label>
                        <input type="text" wire:model="title" placeholder="e.g. Earn FREE Coins"
                               class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                        @error('title') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Description</label>
                        <textarea wire:model="description" rows="2" placeholder="Short advertisement description"
                                  class="w-full resize-none rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30"></textarea>
                        @error('description') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Reward message + amount --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Reward Message</label>
                        <input type="text" wire:model="reward_message" placeholder="Watch the full video to earn 100 Coins."
                               class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                        @error('reward_message') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Reward Amount</label>
                            <input type="number" step="1" min="1" wire:model="reward_amount"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('reward_amount') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Reward Type</label>
                            <input type="text" wire:model="reward_type"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('reward_type') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Video --}}
                    <div class="rounded-lg border border-[#f5c542]/10 bg-black/20 p-4">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <label class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Video</label>
                            <div class="flex gap-1.5">
                                <button type="button" wire:click="$set('video_source', 'upload')"
                                        class="whitespace-nowrap rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-wide transition {{ $video_source === 'upload' ? 'bg-[#f5c542] text-black' : 'border border-[#f5c542]/20 text-[#f5f5f0]/60 hover:border-[#f5c542]/40' }}">
                                    Upload
                                </button>
                                <button type="button" wire:click="$set('video_source', 'external')"
                                        class="whitespace-nowrap rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-wide transition {{ $video_source === 'external' ? 'bg-[#f5c542] text-black' : 'border border-[#f5c542]/20 text-[#f5f5f0]/60 hover:border-[#f5c542]/40' }}">
                                    External Link
                                </button>
                            </div>
                        </div>

                        @if ($video_source === 'upload')
                            <input type="file" wire:model="videoFile" accept="video/mp4,video/quicktime,video/webm"
                                   class="block w-full text-xs text-[#f5f5f0]/70 file:mr-3 file:rounded-lg file:border-0 file:bg-[#f5c542]/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#f5c542] hover:file:bg-[#f5c542]/20">
                            <p class="mt-1 text-[10px] text-[#6b6b6b]">MP4, MOV or WebM, up to 50MB.</p>

                            <div wire:loading wire:target="videoFile" class="mt-2 text-[11px] text-orange-400">
                                <span class="inline-block animate-spin">⟳</span> Uploading...
                            </div>

                            @if ($videoFile)
                                <video src="{{ $videoFile->temporaryUrl() }}" controls class="mt-2 max-h-40 w-full rounded-lg"></video>
                            @elseif ($editingId && $video_url)
                                <video src="{{ $video_url }}" controls class="mt-2 max-h-40 w-full rounded-lg"></video>
                                <p class="mt-1 text-[10px] text-[#6b6b6b]">Current video — choose a new file above to replace it.</p>
                            @endif

                            @error('videoFile') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        @else
                            <input type="text" wire:model="video_url" placeholder="https://cdn.example.com/video.mp4"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('video_url') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- Thumbnail --}}
                    <div class="rounded-lg border border-[#f5c542]/10 bg-black/20 p-4">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <label class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Thumbnail</label>
                            <div class="flex gap-1.5">
                                <button type="button" wire:click="$set('thumbnail_source', 'upload')"
                                        class="whitespace-nowrap rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-wide transition {{ $thumbnail_source === 'upload' ? 'bg-[#f5c542] text-black' : 'border border-[#f5c542]/20 text-[#f5f5f0]/60 hover:border-[#f5c542]/40' }}">
                                    Upload
                                </button>
                                <button type="button" wire:click="$set('thumbnail_source', 'external')"
                                        class="whitespace-nowrap rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-wide transition {{ $thumbnail_source === 'external' ? 'bg-[#f5c542] text-black' : 'border border-[#f5c542]/20 text-[#f5f5f0]/60 hover:border-[#f5c542]/40' }}">
                                    External Link
                                </button>
                            </div>
                        </div>

                        @if ($thumbnail_source === 'upload')
                            <input type="file" wire:model="thumbnailFile" accept="image/*"
                                   class="block w-full text-xs text-[#f5f5f0]/70 file:mr-3 file:rounded-lg file:border-0 file:bg-[#f5c542]/10 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#f5c542] hover:file:bg-[#f5c542]/20">
                            <p class="mt-1 text-[10px] text-[#6b6b6b]">JPG, PNG or WebP, up to 5MB.</p>

                            <div wire:loading wire:target="thumbnailFile" class="mt-2 text-[11px] text-orange-400">
                                <span class="inline-block animate-spin">⟳</span> Uploading...
                            </div>

                            @if ($thumbnailFile)
                                <img src="{{ $thumbnailFile->temporaryUrl() }}" class="mt-2 max-h-32 rounded-lg object-cover" />
                            @elseif ($editingId && $thumbnail_url)
                                <img src="{{ $thumbnail_url }}" class="mt-2 max-h-32 rounded-lg object-cover" />
                                <p class="mt-1 text-[10px] text-[#6b6b6b]">Current thumbnail — choose a new file above to replace it.</p>
                            @endif

                            @error('thumbnailFile') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        @else
                            <input type="text" wire:model="thumbnail_url" placeholder="https://cdn.example.com/thumb.jpg"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('thumbnail_url') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">CTA Text</label>
                            <input type="text" wire:model="cta_text" placeholder="Install Now"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('cta_text') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">CTA Subtitle</label>
                            <input type="text" wire:model="cta_subtitle" placeholder="Free Download"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('cta_subtitle') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Click URL</label>
                        <input type="text" wire:model="click_url" placeholder="https://example.com/install"
                               class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                        @error('click_url') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Duration + orientation --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Duration</label>
                            <select wire:model.live="duration_seconds"
                                    class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                                <option value="10">10 seconds</option>
                                <option value="20">20 seconds</option>
                                <option value="30">30 seconds</option>
                            </select>
                            <p class="mt-1 text-[10px] text-[#6b6b6b]">KES {{ number_format($this->costPerViewPreview, 2) }} / completed view</p>
                            @error('duration_seconds') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Orientation</label>
                            <select wire:model="orientation"
                                    class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                            @error('orientation') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Toggles --}}
                    <div class="flex items-center justify-between rounded-lg border border-[#f5c542]/10 bg-black/20 px-4 py-3">
                        <div>
                            <div class="text-xs font-semibold text-[#f5f5f0]">Allow Skip</div>
                            <div class="text-[10px] text-[#6b6b6b]">Rewarded ads should normally stay non-skippable</div>
                        </div>
                        <label class="relative inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="skip_allowed" class="peer sr-only">
                            <div class="h-5 w-9 rounded-full bg-gray-700 transition-colors peer-checked:bg-[#f5c542]"></div>
                            <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-[#f5c542]/10 bg-black/20 px-4 py-3">
                        <div>
                            <div class="text-xs font-semibold text-[#f5f5f0]">Require Full Completion</div>
                            <div class="text-[10px] text-[#6b6b6b]">Reward only grants once the video finishes</div>
                        </div>
                        <label class="relative inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="reward_requires_completion" class="peer sr-only">
                            <div class="h-5 w-9 rounded-full bg-gray-700 transition-colors peer-checked:bg-[#f5c542]"></div>
                            <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between rounded-lg border border-[#f5c542]/10 bg-black/20 px-4 py-3">
                        <div>
                            <div class="text-xs font-semibold text-[#f5f5f0]">Active</div>
                            <div class="text-[10px] text-[#6b6b6b]">Eligible to be served immediately</div>
                        </div>
                        <label class="relative inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="is_active" class="peer sr-only">
                            <div class="h-5 w-9 rounded-full bg-gray-700 transition-colors peer-checked:bg-[#f5c542]"></div>
                            <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
                        </label>
                    </div>
                </form>

                {{-- Footer — fixed, always visible, never scrolled out of reach --}}
                <div class="flex flex-shrink-0 gap-3 border-t border-[#f5c542]/10 px-4 py-4 sm:px-6">
                    <button type="button" wire:click="closeModal"
                            class="flex-1 rounded-lg border border-[#f5c542]/20 py-2.5 text-xs font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40">
                        Cancel
                    </button>
                    <button type="submit" form="ad-form"
                            wire:loading.attr="disabled"
                            wire:target="save,videoFile,thumbnailFile"
                            class="btn-casino-primary flex flex-1 items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create Ad' }}</span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-1.5">
                            <span class="inline-block animate-spin">⟳</span> Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
