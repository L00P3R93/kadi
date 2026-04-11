<div wire:poll.30000ms class="bg-[#111] border border-[#222] rounded-xl p-4">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-4">
        <span class="text-white font-semibold text-sm">🎯 Odds API Credits</span>
        <button wire:click="refresh()" class="text-gray-500 hover:text-[#f5c542] transition text-sm">
            🔄 <span wire:loading.remove wire:target="refresh">Refresh</span><span wire:loading wire:target="refresh">...</span>
        </button>
    </div>

    {{-- Low credits warning --}}
    @if($this->isLow())
        <div class="bg-red-950 border border-red-700 text-red-400 text-xs rounded-lg px-3 py-2 mb-4 flex items-center gap-2">
            ⚠️ Low credits remaining — consider upgrading your plan.
        </div>
    @endif

    {{-- Progress bar --}}
    <div class="flex justify-between items-center mb-1">
        <span class="text-xs text-gray-500">Credits Used</span>
        <span class="text-xs font-bold {{ $this->isLow() ? 'text-red-400' : 'text-[#f5c542]' }}">
            {{ $this->percentUsed() }}%
        </span>
    </div>
    <div class="bg-[#222] rounded-full h-2.5 w-full">
        <div
            class="h-2.5 rounded-full transition-all duration-500 {{ $this->isLow() ? 'bg-red-500' : 'bg-gradient-to-r from-[#f5c542] to-[#ffde74]' }}"
            style="width: {{ min($this->percentUsed(), 100) }}%"
        ></div>
    </div>

    {{-- Stats --}}
    <div class="mt-3 grid grid-cols-2 gap-3">
        <div class="bg-[#1a1a1a] rounded-lg p-3 text-center">
            <div class="text-2xl font-bold text-white">{{ number_format($used) }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Used</div>
        </div>
        <div class="bg-[#1a1a1a] rounded-lg p-3 text-center">
            <div class="text-2xl font-bold {{ $this->isLow() ? 'text-red-400' : 'text-[#f5c542]' }}">
                {{ number_format($remaining) }}
            </div>
            <div class="text-xs text-gray-500 mt-0.5">Remaining</div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="mt-4 pt-3 border-t border-[#222] flex justify-between items-center">
        <span class="text-xs text-gray-600">Plan: {{ number_format($plan) }} credits/month</span>
        <span class="text-xs text-gray-600">
            Updated {{ $updatedAt ? \Carbon\Carbon::parse($updatedAt)->diffForHumans() : 'Never' }}
        </span>
    </div>

</div>
