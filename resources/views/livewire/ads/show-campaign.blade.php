<div class="min-h-screen bg-[#0a0a0a] pt-10 pb-20">

    {{-- Ambient background glow --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 h-[420px] -z-0"
         style="background: radial-gradient(60% 60% at 50% 0%, rgba(245,197,66,0.06) 0%, transparent 70%);"></div>

    <div class="relative mx-auto max-w-6xl px-4 sm:px-6">

        {{-- ═══ Back link ═══ --}}
        <a href="{{ route('ad-campaigns') }}" wire:navigate
           class="mb-4 inline-flex items-center gap-1.5 text-xs text-[#6b6b6b] transition hover:text-[#f5c542]">
            <span>←</span> All Campaigns
        </a>

        {{-- ═══ Header ═══ --}}
        <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    @php
                        $status = $campaign->status;
                    @endphp
                    <span class="inline-flex items-center rounded-full border {{ $status->color() }} px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                        {{ $status->label() }}
                    </span>
                    <span class="text-[11px] text-[#6b6b6b]">{{ $campaign->adCategory->name ?? 'Uncategorized' }}</span>
                </div>
                <h1 class="break-words font-cinzel text-2xl font-bold text-[#f5f5f0] sm:text-3xl">{{ $campaign->name }}</h1>
                @if ($campaign->adProfile)
                    <p class="mt-1 text-sm text-[#6b6b6b]">{{ $campaign->adProfile->company_name ?? 'Advertiser #'.$campaign->adProfile->id }}</p>
                @endif
            </div>
        </div>

        @if ($campaign->status === \App\Enums\CampaignStatus::Rejected && $campaign->rejection_reason)
            <div class="mb-6 rounded-lg border border-red-800 bg-red-950/40 p-4 text-xs text-red-400">
                <span class="font-semibold uppercase tracking-wide">Rejected:</span> {{ $campaign->rejection_reason }}
            </div>
        @endif

        {{-- ═══ Budget stats ═══ --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="glass-card p-5">
                <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Total Budget</div>
                <div class="mt-1 font-cinzel text-xl font-black text-[#f5f5f0]">KES {{ number_format($campaign->total_budget, 2) }}</div>
            </div>
            <div class="glass-card p-5">
                <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Remaining</div>
                <div class="mt-1 font-cinzel text-xl font-black text-[#f5c542]">KES {{ number_format($campaign->escrowed_budget, 2) }}</div>
            </div>
            <div class="glass-card p-5">
                <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Spent</div>
                <div class="mt-1 font-cinzel text-xl font-black text-[#f5f5f0]/70">KES {{ number_format($campaign->spent_budget, 2) }}</div>
            </div>
        </div>

        {{-- ═══ Meta details ═══ --}}
        <div class="glass-card mb-10 grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Priority</div>
                <div class="mt-1 text-sm text-[#f5f5f0]">{{ $campaign->priority }} / 10</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Frequency Cap</div>
                <div class="mt-1 text-sm text-[#f5f5f0]">{{ $campaign->frequency_cap }} views / user / day</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Starts</div>
                <div class="mt-1 text-sm text-[#f5f5f0]">{{ $campaign->starts_at?->format('d M Y, H:i') }}</div>
            </div>
            <div>
                <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Ends</div>
                <div class="mt-1 text-sm text-[#f5f5f0]">{{ $campaign->ends_at?->format('d M Y, H:i') }}</div>
            </div>
        </div>

        {{-- ═══ Ads management (embedded) ═══ --}}
        <livewire:ads.adverts :campaign="$campaign" />
    </div>
</div>
