<div class="bg-[#111] flex flex-col h-full">
    {{-- Header --}}
    <div class="px-4 py-3 border-b border-[#222] flex justify-between items-center flex-shrink-0">
        <span class="text-white font-bold text-xs tracking-widest">BET SLIP</span>
        <span class="bg-[#f5c542] text-black text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
            {{ count($selections) }}
        </span>
    </div>

    {{-- Selections --}}
    <div class="flex-1 overflow-y-auto px-3 py-3">
        @forelse($selections as $eventId => $sel)
            <div class="bg-[#1a1a1a] rounded-lg p-3 mb-2 border border-[#333]">
                <div class="flex justify-between items-start">
                    <span class="text-xs text-gray-400 leading-tight flex-1 mr-2">{{ $sel['label'] }}</span>
                    <button
                        wire:click="removeSelection('{{ $eventId }}')"
                        class="text-gray-500 hover:text-red-400 transition text-lg leading-none"
                    >×</button>
                </div>
                <div class="text-white text-sm font-semibold mt-1">{{ $sel['team'] }}</div>
                <div class="text-[#f5c542] font-bold text-xl mt-0.5">{{ number_format($sel['price'], 2) }}</div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center mt-10 text-center">
                <div class="text-4xl mb-2">🎯</div>
                <div class="text-gray-600 text-sm">Your bet slip is empty</div>
                <div class="text-gray-700 text-xs mt-1">Click an odds button to add a selection</div>
            </div>
        @endforelse
    </div>

    {{-- Footer --}}
    <div class="flex-shrink-0 px-4 py-4 border-t border-[#222]">
        <label class="text-xs text-gray-400 mb-1 block">Stake (KES)</label>
        <input
            wire:model.live="stake"
            type="number"
            min="0"
            step="1"
            placeholder="0.00"
            class="w-full bg-[#1a1a1a] text-white border border-[#f5c542] rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#f5c542]"
        >

        <div class="mt-2 flex justify-between items-center">
            <span class="text-xs text-gray-400">Potential Payout</span>
            <span class="text-[#f5c542] font-bold text-sm">KES {{ number_format($this->potentialPayout(), 2) }}</span>
        </div>

        {{-- Sign-in prompt (replaces PLACE BET) --}}
        <div class="mt-3 bg-[#1a1200] border border-[#f5c542]/30 rounded-lg p-3 text-center">
            <div class="text-[#f5c542] text-xs font-semibold mb-2">🔐 Sign in to place bets</div>
            <div class="text-gray-500 text-xs mb-3">Create a free account to start winning</div>
            <div class="flex gap-2">
                <a href="{{ route('login') }}" wire:navigate
                   class="flex-1 text-center bg-transparent border border-[#f5c542] text-[#f5c542] font-bold py-2 rounded hover:bg-[#f5c542] hover:text-black transition text-xs">
                    Sign In
                </a>
                <a href="{{ route('register') }}" wire:navigate
                   class="flex-1 text-center bg-[#f5c542] text-black font-bold py-2 rounded hover:bg-[#ffde74] transition text-xs">
                    Register
                </a>
            </div>
        </div>

        <button
            wire:click="clearSlip()"
            class="mt-2 block w-full text-xs text-gray-600 hover:text-red-400 transition cursor-pointer text-center"
        >
            Clear All
        </button>
    </div>
</div>
