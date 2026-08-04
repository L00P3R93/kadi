<div
    x-data="{ confirmDeactivateId: null }"
    class="min-h-screen bg-[#0a0a0a] pt-14 pb-20"
>
    {{-- Ambient background glow --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 h-[420px] -z-0"
         style="background: radial-gradient(60% 60% at 50% 0%, rgba(245,197,66,0.06) 0%, transparent 70%);"></div>

    <div class="relative mx-auto max-w-6xl px-6">

        {{-- ═══ Header ═══ --}}
        <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-cinzel text-3xl font-bold text-[#f5f5f0]">Ad Campaigns</h1>
                <p class="mt-1 text-sm text-[#6b6b6b]">Campaigns for adverts</p>
            </div>

            <button type="button" wire:click="openCreateModal" class="btn-casino-primary flex items-center gap-2 rounded-full px-5 py-2.5 text-xs whitespace-nowrap">
                <span class="text-base leading-none">+</span> New Campaign
            </button>
        </div>

        {{-- ═══ Success banner ═══ --}}
        @if ($successMessage)
            <div class="mb-6 flex items-center gap-2 rounded-lg border border-green-800 bg-green-950/30 p-4 text-xs text-green-400">
                <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                {{ $successMessage }}
            </div>
        @endif

        {{-- ═══ Campaigns table ═══ --}}
        <div class="glass-card overflow-hidden p-0" wire:loading.class="opacity-50" wire:target="search,status,approval,perPage,sortBy,gotoPage,previousPage,nextPage">
            <div class="overflow-x-auto">
                <livewire:tables.campaigns-table />
            </div>
        </div>


        <p class="mt-4 text-[11px] text-[#6b6b6b]">
            {{-- TODO: Some info text here about editing campaign status and the effect of this --}}
        </p>
    </div>

    {{-- ═══ Create / Edit modal ═══ --}}
    @if ($showFormModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.closeModal()"
            wire:click.self="closeModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-3 py-6 sm:px-4 backdrop-blur-sm"
        >
            <div class="glass-card relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-[#f5c542]/30">

                {{-- Header — fixed, never scrolls --}}
                <div class="flex flex-shrink-0 items-center justify-between gap-4 border-b border-[#f5c542]/10 px-4 py-4 sm:px-6">
                    <h3 class="font-cinzel text-lg font-bold text-[#f5f5f0]">
                        {{ $editingId ? 'Edit Campaign' : 'New Campaign' }}
                    </h3>
                    <button type="button" wire:click="closeModal"
                            class="flex-shrink-0 text-[#6b6b6b] transition hover:text-[#f5c542]">✕</button>
                </div>

                {{-- Body — the only part that scrolls --}}
                <form wire:submit="save" id="campaign-form" class="min-h-0 flex-1 space-y-4 overflow-y-auto px-4 py-4 sm:px-6">

                    {{-- Name --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Campaign Name</label>
                        <input type="text" wire:model="name" placeholder="e.g. August VIP Push"
                               class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                        @error('name') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Advertiser --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Advertiser</label>
                        <select wire:model="ad_profile_id"
                                class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            <option value="">Select an advertiser...</option>
                            @foreach ($adProfiles as $profile)
                                {{-- NOTE: assuming AdProfile has a company_name column — swap for
                                     whatever field actually identifies the advertiser if not. --}}
                                <option value="{{ $profile->id }}">{{ $profile->company_name ?? ('Advertiser Profile #'.$profile->id) }}</option>
                            @endforeach
                        </select>
                        @error('ad_profile_id') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Category</label>
                        <select wire:model="ad_category_id"
                                class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            <option value="">Select a category...</option>
                            @foreach ($adCategories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }} ({{ number_format($category->pricing_multiplier, 2) }}x){{ $category->requires_approval ? ' — requires review' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if (! $editingId)
                            <p class="mt-1 text-[10px] text-[#6b6b6b]">Alcohol/Political campaigns start as "Pending Review" — everything else goes live immediately.</p>
                        @endif
                        @error('ad_category_id') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status (edit only) --}}
                    @if ($editingId)
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Status</label>
                            <select wire:model="status"
                                    class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                                @foreach ($statuses as $case)
                                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                                @endforeach
                            </select>
                            @error('status') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Budget + Priority --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Total Budget (KES)</label>
                            <input type="number" step="1" min="1" wire:model="total_budget" placeholder="10000"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('total_budget') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Priority (1–10)</label>
                            <input type="number" step="1" min="1" max="10" wire:model="priority"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('priority') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Frequency cap --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Frequency Cap</label>
                        <input type="number" step="1" min="1" wire:model="frequency_cap"
                               class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                        <p class="mt-1 text-[10px] text-[#6b6b6b]">Max completed views per user, per 24 hours.</p>
                        @error('frequency_cap') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Schedule --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Starts</label>
                            <input type="datetime-local" wire:model="starts_at"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('starts_at') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Ends</label>
                            <input type="datetime-local" wire:model="ends_at"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('ends_at') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </form>

                {{-- Footer — fixed, always visible, never scrolled out of reach --}}
                <div class="flex flex-shrink-0 gap-3 border-t border-[#f5c542]/10 px-4 py-4 sm:px-6">
                    <button type="button" wire:click="closeModal"
                            class="flex-1 rounded-lg border border-[#f5c542]/20 py-2.5 text-xs font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40">
                        Cancel
                    </button>
                    <button type="submit" form="campaign-form"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="btn-casino-primary flex flex-1 items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create Campaign' }}</span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-1.5">
                            <span class="inline-block animate-spin">⟳</span> Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
