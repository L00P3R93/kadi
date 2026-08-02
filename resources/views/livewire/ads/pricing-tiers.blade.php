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
                <h1 class="font-cinzel text-3xl font-bold text-[#f5f5f0]">Pricing Tiers</h1>
                <p class="mt-1 text-sm text-[#6b6b6b]">Pricing tiers define the base cost of rewarded video ads.</p>
            </div>

            <button type="button" wire:click="openCreateModal" class="btn-casino-primary flex items-center gap-2 rounded-full px-5 py-2.5 text-xs whitespace-nowrap">
                <span class="text-base leading-none">+</span> New Tier
            </button>
        </div>

        {{-- ═══ Success banner ═══ --}}
        @if ($successMessage)
            <div class="mb-6 flex items-center gap-2 rounded-lg border border-green-800 bg-green-950/30 p-4 text-xs text-green-400">
                <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                {{ $successMessage }}
            </div>
        @endif

        {{-- ═══ Categories table ═══ --}}
        <div class="glass-card overflow-hidden p-0" wire:loading.class="opacity-50" wire:target="search,status,approval,perPage,sortBy,gotoPage,previousPage,nextPage">
            <div class="overflow-x-auto">
                <livewire:tables.pricing-tiers-table />
            </div>
        </div>
    </div>

    {{-- ═══ Create / Edit modal ═══ --}}
    @if ($showFormModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.closeModal()"
            wire:click.self="closeModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        >
            <div class="glass-card relative w-full max-w-md rounded-2xl border border-[#f5c542]/30 p-6">
                <button type="button" wire:click="closeModal"
                        class="absolute right-4 top-4 text-[#6b6b6b] transition hover:text-[#f5c542]">✕</button>

                <h3 class="mb-5 font-cinzel text-lg font-bold text-[#f5f5f0]">
                    {{ $editingId ? 'Edit Tier' : 'New Tier' }}
                </h3>

                <form wire:submit="save" class="space-y-4">

                    {{-- Duration Seconds --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Duration (in seconds)</label>
                        <input type="text" wire:model="duration_seconds" placeholder="e.g. 10, 20 or 30"
                               class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                        <p class="mt-1 text-[10px] text-[#6b6b6b]">Only 10 seconds, 20 seconds and 30 seconds are allowed.</p>
                        @error('duration_seconds') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Base Cost --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Pricing Multiplier</label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0.1" max="10" wire:model="base_cost"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 pr-8 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-[#6b6b6b]">×</span>
                        </div>
                        <p class="mt-1 text-[10px] text-[#6b6b6b]"></p>
                        @error('base_cost') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeModal"
                                class="flex-1 rounded-lg border border-[#f5c542]/20 py-2.5 text-xs font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40">
                            Cancel
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="btn-casino-primary flex flex-1 items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Create Tier' }}</span>
                            <span wire:loading wire:target="save" class="inline-flex items-center gap-1.5">
                                <span class="inline-block animate-spin">⟳</span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
