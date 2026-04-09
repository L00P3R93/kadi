<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#f5f5f0]" style="font-family:'Cinzel',serif;">Games</h1>
        <p class="mt-1 text-sm text-[#6b6b6b]">Choose a game to play. More titles launching soon.</p>
    </div>

    @include('partials.games-grid', ['games' => $games])

    <flux:modal wire:model="showComingSoonModal" class="max-w-sm">
        <div class="p-6 space-y-5">
            <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family:'Cinzel',serif;">💰 {{ $selectedGame }}</h3>
            <p class="text-sm text-[#6b6b6b]">Coming Soon. Stay tuned for this game's launch!</p>
            <flux:button wire:click="$set('showComingSoonModal', false)" variant="ghost" class="w-full">Close</flux:button>
        </div>
    </flux:modal>
</div>
