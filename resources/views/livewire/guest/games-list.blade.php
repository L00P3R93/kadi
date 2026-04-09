<div
    class="mx-auto max-w-7xl px-6 pt-28 pb-16"
    x-data="{ loaded: false }"
    x-init="$nextTick(() => { loaded = true })"
>
    {{-- Hero header --}}
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-[#f5f5f0]" style="font-family:'Cinzel',serif;">Our Games</h1>
        <div class="mx-auto mt-3 h-0.5 w-16 bg-[#f5c542] rounded-full"></div>
    </div>

    {{-- Skeleton shown until Alpine hydrates --}}
    <div x-show="!loaded" class="games-grid" aria-hidden="true">
        @foreach (range(1, 10) as $i)
            <div class="game-card-skeleton"></div>
        @endforeach
    </div>

    {{-- Real grid --}}
    <div x-show="loaded" x-cloak>
        @include('partials.games-grid', ['games' => $games])
    </div>

    <flux:modal wire:model="showComingSoonModal" class="max-w-sm">
        <div class="p-6 space-y-5">
            <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family:'Cinzel',serif;">💰 {{ $selectedGame }}</h3>
            <p class="text-sm text-[#6b6b6b]">Coming Soon. Stay tuned for this game's launch!</p>
            <flux:button wire:click="$set('showComingSoonModal', false)" variant="ghost" class="w-full">Close</flux:button>
        </div>
    </flux:modal>
</div>
