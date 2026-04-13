<div class="w-full bg-[#111]">

    {{-- STICKY HEADER — only the header is sticky --}}
    <div class="sticky top-0 z-10 bg-[#0f0f0f] border-b border-[#222] px-4 py-3
                flex justify-between items-center">
        <span class="font-cinzel text-xs font-bold tracking-widest text-white uppercase">Bet Slip</span>
        <span
            x-data
            x-text="$store.betSlip.count()"
            class="bg-[#f5c542] text-black text-xs font-black rounded-full w-5 h-5 flex items-center justify-center"
        ></span>
    </div>

    {{-- Flash messages --}}
    @if(session('bet_success'))
        <div class="mx-3 mt-3 bg-green-900/30 border border-green-600 text-green-400 text-xs rounded px-3 py-2">
            {{ session('bet_success') }}
        </div>
    @endif
    @if(session('bet_error'))
        <div class="mx-3 mt-3 bg-red-900/30 border border-red-600 text-red-400 text-xs rounded px-3 py-2">
            {{ session('bet_error') }}
        </div>
    @endif

    {{-- BODY — grows naturally --}}
    <div class="px-3 py-3" x-data>

        {{-- Empty state --}}
        <template x-if="$store.betSlip.count() === 0">
            <div class="flex flex-col items-center justify-center py-10 text-gray-600 text-center">
                <span class="text-4xl mb-2">🎯</span>
                <div class="text-sm">Your bet slip is empty</div>
                <div class="text-xs mt-1 text-gray-700">Click any odds to add a selection</div>
            </div>
        </template>

        <template x-if="$store.betSlip.count() > 0">
            <div class="space-y-2">
                <template x-for="[key, sel] in Object.entries($store.betSlip.selections)" :key="key">
                    <div
                        x-data="{ flash: false }"
                        x-on:bet-slip-updated.window="flash = true; setTimeout(() => flash = false, 400)"
                        :class="flash ? 'ring-1 ring-[#f5c542]/50' : ''"
                        class="relative bg-[#1a1a1a] rounded-lg border border-[#2a2a2a] overflow-hidden
                               hover:border-[#333] transition-all duration-300"
                    >
                        {{-- Gold left accent bar --}}
                        <div class="absolute left-0 top-0 bottom-0 w-[3px] bg-gradient-to-b from-[#f5c542] to-[#f5c542]/40"></div>
                        <div class="pl-3 pr-3 pt-2.5 pb-2.5">
                            {{-- Row 1: Event name + remove --}}
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <span class="text-xs text-white font-semibold leading-snug flex-1"
                                      x-text="sel.homeTeam + ' vs ' + sel.awayTeam"></span>
                                <button
                                    @click="$store.betSlip.remove(key)"
                                    class="flex-shrink-0 w-5 h-5 rounded flex items-center justify-center
                                           text-gray-600 hover:text-red-400 hover:bg-red-400/10 transition-all duration-150
                                           text-base leading-none mt-0.5"
                                    aria-label="Remove selection"
                                >×</button>
                            </div>
                            {{-- Row 2: Market • Outcome --}}
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="text-[10px] text-[#f5c542]/70 font-medium" x-text="sel.marketLabel"></span>
                                <span class="text-gray-600 text-[10px]">•</span>
                                <span class="text-[10px] text-gray-300 font-semibold" x-text="sel.team"></span>
                            </div>
                            {{-- Row 3: Time • Status --}}
                            <div class="flex items-center gap-1.5 mb-2.5">
                                <svg class="w-3 h-3 text-gray-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-[10px] text-gray-500"
                                      x-text="sel.commenceTime ? $store.betSlip.formatTime(sel.commenceTime) : ''"></span>
                                <span class="text-gray-700 text-[10px]">•</span>
                                <span x-show="sel.isLive"
                                      class="text-[9px] font-bold text-red-400 uppercase tracking-wide">● Live</span>
                                <span x-show="!sel.isLive"
                                      class="text-[9px] text-gray-600 uppercase tracking-wide">Upcoming</span>
                            </div>
                            {{-- Row 4: Odds --}}
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-gray-600 uppercase tracking-widest font-medium">Odds</span>
                                <span class="font-cinzel font-black text-base text-[#f5c542]"
                                      x-text="sel.price.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

    </div>

    {{-- FOOTER — flows naturally after selections --}}
    <div class="border-t border-[#222] bg-[#0f0f0f]" x-data>

        {{-- Show footer only when there are selections --}}
        <template x-if="$store.betSlip.count() > 0">
            <div class="px-4 pt-4 pb-4 space-y-4">

                {{-- Stake input section --}}
                <div>
                    <label class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold block mb-1.5">
                        Stake (KES)
                    </label>

                    <input
                        wire:model.live="stake"
                        type="number"
                        min="0"
                        step="1"
                        placeholder="Enter amount..."
                        class="w-full bg-[#1a1a1a] text-white border border-[#f5c542]/40 rounded-lg px-3 py-2.5 text-sm
                               focus:outline-none focus:border-[#f5c542] focus:ring-1 focus:ring-[#f5c542]/30
                               placeholder-gray-600 transition"
                    />

                    {{-- Quick stake buttons --}}
                    <div class="grid grid-cols-4 gap-1.5 mt-2">
                        @foreach([50, 100, 500, 1000] as $amount)
                            <button
                                wire:click="setStake({{ $amount }})"
                                class="text-xs font-bold py-1.5 rounded border transition-all duration-100
                                       {{ (string)$stake === (string)$amount
                                           ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                           : 'bg-[#1a1a1a] border-[#333] text-gray-300 hover:bg-[#f5c542]/10 hover:border-[#f5c542]/50 hover:text-[#f5c542]' }}"
                            >
                                {{ number_format($amount) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Bet totals summary --}}
                <div class="bg-[#1a1a1a] rounded-lg border border-[#2a2a2a] overflow-hidden">
                    <div class="divide-y divide-[#222]">

                        {{-- Bet Type --}}
                        <div class="flex justify-between items-center px-3 py-2.5">
                            <span class="text-[11px] text-gray-500 uppercase tracking-wide font-medium">Bet Type</span>
                            <span class="text-xs font-bold text-white">{{ $this->getBetType() }}</span>
                        </div>

                        {{-- Selections --}}
                        <div class="flex justify-between items-center px-3 py-2.5">
                            <span class="text-[11px] text-gray-500 uppercase tracking-wide font-medium">Selections</span>
                            <span class="text-xs font-bold text-white">{{ $this->getSelectionCount() }}</span>
                        </div>

                        {{-- Total Odds --}}
                        <div class="flex justify-between items-center px-3 py-2.5">
                            <span class="text-[11px] text-gray-500 uppercase tracking-wide font-medium">Total Odds</span>
                            <span class="text-xs font-black text-[#f5c542]">{{ number_format($this->getTotalOdds(), 2) }}</span>
                        </div>

                        {{-- Stake --}}
                        <div class="flex justify-between items-center px-3 py-2.5">
                            <span class="text-[11px] text-gray-500 uppercase tracking-wide font-medium">Stake</span>
                            <span class="text-xs font-bold text-white">
                                KES {{ is_numeric($stake) && $stake > 0 ? number_format((float)$stake, 2) : '0.00' }}
                            </span>
                        </div>

                        {{-- Possible Win --}}
                        <div class="flex justify-between items-center px-3 py-3 bg-[#f5c542]/5">
                            <span class="text-[11px] text-[#f5c542] uppercase tracking-wide font-bold">Possible Win</span>
                            <span class="text-sm font-black text-[#f5c542]">
                                KES {{ number_format($this->getPossibleWin(), 2) }}
                            </span>
                        </div>

                    </div>
                </div>

                {{-- Place Bet button --}}
                <button
                    wire:click="placeBet"
                    @disabled($this->getSelectionCount() === 0 || !is_numeric($stake) || $stake <= 0)
                    class="w-full bg-[#f5c542] text-black font-black py-3 rounded-lg
                           hover:bg-[#ffde74] transition-colors duration-150 text-sm tracking-widest uppercase
                           disabled:opacity-40 disabled:cursor-not-allowed shadow-lg shadow-[#f5c542]/20"
                >
                    Place {{ $this->getBetType() }}
                </button>

                {{-- Clear All --}}
                <button
                    @click="$store.betSlip.clear()"
                    class="block w-full text-center text-[11px] text-gray-600 hover:text-red-400
                           transition cursor-pointer pb-1"
                >
                    Clear All Selections
                </button>

            </div>
        </template>

        {{-- Empty state footer --}}
        <template x-if="$store.betSlip.count() === 0">
            <div class="px-4 py-4">
                <div class="w-full bg-[#1a1a1a] border border-dashed border-[#333] rounded-lg
                            py-3 text-center text-[11px] text-gray-600">
                    Add selections to see your bet summary
                </div>
            </div>
        </template>

    </div>

</div>
