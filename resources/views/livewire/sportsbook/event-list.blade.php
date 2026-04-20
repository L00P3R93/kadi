<div class="flex flex-col h-full">

    {{-- Top Bar --}}
    <div class="sticky top-0 z-10 bg-[#111] border-b border-[#222] px-4 py-3 flex justify-between items-center flex-shrink-0">
        <div class="flex items-center gap-2">
            <button
                @click="sidenavOpen = !sidenavOpen"
                class="lg:hidden flex items-center justify-center w-7 h-7 text-gray-400 hover:text-[#f5c542] transition-colors"
                aria-label="Open sports menu"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </button>
            <span class="text-white font-bold" x-text="$store.sportsbook.getSportTitle()"></span>
        </div>
        <div class="flex items-center gap-3">
            <div x-data="{ tab: 'upcoming' }" class="flex gap-2">
                <button
                    @click="tab = 'upcoming'"
                    :class="tab === 'upcoming' ? 'bg-[#f5c542] text-black font-bold' : 'bg-[#222] text-gray-400 hover:bg-[#333]'"
                    class="rounded px-3 py-1 text-xs transition"
                >Upcoming</button>
                <button
                    @click="tab = 'live'"
                    :class="tab === 'live' ? 'bg-[#f5c542] text-black font-bold' : 'bg-[#222] text-gray-400 hover:bg-[#333]'"
                    class="rounded px-3 py-1 text-xs transition"
                >Live</button>
            </div>
        </div>
    </div>

    {{-- Loading state --}}
    <div x-show="!$store.sportsbook.loaded" class="flex-1 flex items-center justify-center py-20">
        <div class="text-[#f5c542] text-sm animate-pulse">Loading odds...</div>
    </div>

    {{-- Event list --}}
    <div x-show="$store.sportsbook.loaded" class="flex-1 overflow-y-auto">

        <template x-for="event in $store.sportsbook.getEvents()" :key="event.id">
            <div
                class="border-b border-[#1a1a1a]"
                x-data="{
                    h2h: {}, sp: {}, tot: {},
                    init() {
                        this.h2h = $store.sportsbook.h2h(event);
                        this.sp  = $store.sportsbook.spreads(event);
                        this.tot = $store.sportsbook.totals(event);
                    }
                }"
            >

                {{-- ══ MOBILE LAYOUT ══ --}}
                <div class="md:hidden">

                    {{-- Meta + Teams header --}}
                    <div class="px-3 pt-2.5 pb-2">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-[9px] bg-[#222] text-[#f5c542] px-1.5 py-0.5 rounded font-bold uppercase tracking-wide truncate max-w-[90px]"
                                  x-text="event.sport_title"></span>
                            <span class="text-[10px] text-gray-500" x-text="$store.sportsbook.formatTime(event.commence_time)"></span>
                            <span x-show="$store.sportsbook.isLive(event.commence_time)"
                                  class="bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded animate-pulse">LIVE</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-white font-semibold text-sm flex-1 truncate" x-text="event.home_team"></span>
                            <span class="text-gray-600 text-[10px] font-bold flex-shrink-0 px-1">vs</span>
                            <span class="text-white font-semibold text-sm flex-1 truncate text-right" x-text="event.away_team"></span>
                        </div>
                    </div>

                    {{-- H2H Market Section --}}
                    <template x-if="h2h.home || h2h.draw || h2h.away">
                        <div class="mx-3 mb-1.5 rounded overflow-hidden border border-[#222]">
                            <div class="bg-[#0d0d0d] px-2.5 py-1 border-b border-[#222]">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400" x-text="h2h.title"></span>
                            </div>
                            <div class="flex">
                                <template x-for="slot in [{label:'Home',outcome:h2h.home},{label:'Draw',outcome:h2h.draw},{label:'Away',outcome:h2h.away}]" :key="slot.label">
                                    <template x-if="slot.outcome">
                                        <button
                                            @click.stop="$store.betSlip.add(event.id, event.id+'_h2h', slot.outcome.name, slot.outcome.price, event.home_team, event.away_team, 'h2h', h2h.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                            :class="$store.betSlip.isSelected(event.id, slot.outcome.name, 'h2h') ? 'bg-[#f5c542] text-black' : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                            class="flex flex-col items-center flex-1 py-2 border-r border-[#222] last:border-r-0 transition-colors duration-75 cursor-pointer"
                                        >
                                            <span class="text-[8px] font-semibold leading-tight text-center px-0.5 truncate w-full"
                                                  :class="$store.betSlip.isSelected(event.id, slot.outcome.name, 'h2h') ? 'text-black/70' : 'text-gray-400'"
                                                  x-text="slot.outcome.name.length > 12 ? slot.outcome.name.substring(0,11)+'…' : slot.outcome.name.toUpperCase()"></span>
                                            <span class="text-sm font-black leading-tight mt-0.5" x-text="parseFloat(slot.outcome.price).toFixed(2)"></span>
                                        </button>
                                    </template>
                                    <template x-if="!slot.outcome">
                                        <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222] last:border-r-0">
                                            <span class="text-[8px] text-gray-700" x-text="slot.label.toUpperCase()"></span>
                                            <span class="text-sm text-gray-700 mt-0.5">—</span>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Spreads Market Section --}}
                    <template x-if="sp.home || sp.away">
                        <div class="mx-3 mb-1.5 rounded overflow-hidden border border-[#222]">
                            <div class="bg-[#0d0d0d] px-2.5 py-1 border-b border-[#222]">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400" x-text="sp.title"></span>
                            </div>
                            <div class="flex">
                                <template x-if="sp.home">
                                    <button
                                        @click.stop="$store.betSlip.add(event.id, event.id+'_spreads', sp.home.name+' '+$store.sportsbook.pt(sp.home.point), sp.home.price, event.home_team, event.away_team, 'spreads', sp.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                        :class="$store.betSlip.isSelected(event.id, sp.home.name+' '+$store.sportsbook.pt(sp.home.point), 'spreads') ? 'bg-[#f5c542] text-black' : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 border-r border-[#222] transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold leading-tight"
                                              :class="$store.betSlip.isSelected(event.id, sp.home.name+' '+$store.sportsbook.pt(sp.home.point), 'spreads') ? 'text-black/70' : 'text-gray-400'"
                                              x-text="'HOME '+$store.sportsbook.pt(sp.home.point)"></span>
                                        <span class="text-sm font-black leading-tight mt-0.5" x-text="parseFloat(sp.home.price).toFixed(2)"></span>
                                    </button>
                                </template>
                                <template x-if="!sp.home">
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222]">
                                        <span class="text-[8px] text-gray-700">HOME</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                </template>
                                <template x-if="sp.away">
                                    <button
                                        @click.stop="$store.betSlip.add(event.id, event.id+'_spreads', sp.away.name+' '+$store.sportsbook.pt(sp.away.point), sp.away.price, event.home_team, event.away_team, 'spreads', sp.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                        :class="$store.betSlip.isSelected(event.id, sp.away.name+' '+$store.sportsbook.pt(sp.away.point), 'spreads') ? 'bg-[#f5c542] text-black' : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold leading-tight"
                                              :class="$store.betSlip.isSelected(event.id, sp.away.name+' '+$store.sportsbook.pt(sp.away.point), 'spreads') ? 'text-black/70' : 'text-gray-400'"
                                              x-text="'AWAY '+$store.sportsbook.pt(sp.away.point)"></span>
                                        <span class="text-sm font-black leading-tight mt-0.5" x-text="parseFloat(sp.away.price).toFixed(2)"></span>
                                    </button>
                                </template>
                                <template x-if="!sp.away">
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111]">
                                        <span class="text-[8px] text-gray-700">AWAY</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Totals + More Markets --}}
                    <template x-if="tot.over || tot.under">
                        <div class="mx-3 mb-2.5 rounded overflow-hidden border border-[#222]">
                            <div class="bg-[#0d0d0d] px-2.5 py-1 border-b border-[#222]">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400" x-text="tot.title"></span>
                            </div>
                            <div class="flex">
                                <template x-if="tot.over">
                                    <button
                                        @click.stop="$store.betSlip.add(event.id, event.id+'_totals', 'Over '+$store.sportsbook.pt(tot.over.point), tot.over.price, event.home_team, event.away_team, 'totals', tot.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                        :class="$store.betSlip.isSelected(event.id, 'Over '+$store.sportsbook.pt(tot.over.point), 'totals') ? 'bg-[#f5c542] text-black' : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 border-r border-[#222] transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold leading-tight"
                                              :class="$store.betSlip.isSelected(event.id, 'Over '+$store.sportsbook.pt(tot.over.point), 'totals') ? 'text-black/70' : 'text-gray-400'"
                                              x-text="'OVER '+$store.sportsbook.pt(tot.over.point)"></span>
                                        <span class="text-sm font-black leading-tight mt-0.5" x-text="parseFloat(tot.over.price).toFixed(2)"></span>
                                    </button>
                                </template>
                                <template x-if="!tot.over">
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222]">
                                        <span class="text-[8px] text-gray-700">OVER</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                </template>
                                <template x-if="tot.under">
                                    <button
                                        @click.stop="$store.betSlip.add(event.id, event.id+'_totals', 'Under '+$store.sportsbook.pt(tot.under.point), tot.under.price, event.home_team, event.away_team, 'totals', tot.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                        :class="$store.betSlip.isSelected(event.id, 'Under '+$store.sportsbook.pt(tot.under.point), 'totals') ? 'bg-[#f5c542] text-black' : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 border-r border-[#222] transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold leading-tight"
                                              :class="$store.betSlip.isSelected(event.id, 'Under '+$store.sportsbook.pt(tot.under.point), 'totals') ? 'text-black/70' : 'text-gray-400'"
                                              x-text="'UNDER '+$store.sportsbook.pt(tot.under.point)"></span>
                                        <span class="text-sm font-black leading-tight mt-0.5" x-text="parseFloat(tot.under.price).toFixed(2)"></span>
                                    </button>
                                </template>
                                <template x-if="!tot.under">
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222]">
                                        <span class="text-[8px] text-gray-700">UNDER</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                </template>
                                <button
                                    @click.stop="$store.sportsbook.openModal(event)"
                                    class="flex-shrink-0 w-10 bg-[#0d0d0d] flex items-center justify-center text-gray-500 hover:text-[#f5c542] hover:bg-[#111] transition-colors"
                                    title="More markets"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="!tot.over && !tot.under">
                        <div class="flex justify-end px-3 pb-2.5">
                            <button
                                @click.stop="$store.sportsbook.openModal(event)"
                                class="flex items-center gap-1 text-[10px] text-gray-500 hover:text-[#f5c542] bg-[#0d0d0d] border border-[#222] rounded px-2.5 py-1.5 transition-colors"
                            >
                                More markets
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                </div>

                {{-- ══ DESKTOP LAYOUT ══ --}}
                <div
                    class="hidden md:flex items-stretch px-4 py-3 gap-3 hover:bg-[#0f0f0f] transition-colors cursor-pointer"
                    @click="$store.sportsbook.openModal(event)"
                >
                    {{-- LEFT: Match info --}}
                    <div class="flex-shrink-0 w-44 flex flex-col justify-center">
                        <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                            <span class="text-[9px] bg-[#222] text-[#f5c542] px-1.5 py-0.5 rounded font-bold uppercase tracking-wide truncate max-w-[90px]"
                                  x-text="event.sport_title"></span>
                            <span class="text-[9px] text-gray-500" x-text="$store.sportsbook.formatTime(event.commence_time)"></span>
                            <span x-show="$store.sportsbook.isLive(event.commence_time)"
                                  class="bg-red-600 text-white text-[8px] font-bold px-1 py-0.5 rounded animate-pulse">LIVE</span>
                        </div>
                        <div class="text-white font-semibold text-xs leading-snug truncate" x-text="event.home_team"></div>
                        <div class="text-gray-600 text-[9px] my-0.5">vs</div>
                        <div class="text-white font-semibold text-xs leading-snug truncate" x-text="event.away_team"></div>
                    </div>

                    {{-- RIGHT: Market groups --}}
                    <div class="flex items-center flex-1 justify-end gap-0">

                        {{-- H2H Group --}}
                        <template x-if="h2h.home || h2h.draw || h2h.away">
                            <div class="flex flex-col items-center px-2 border-l border-[#1e1e1e]">
                                <span class="text-[8px] font-bold uppercase tracking-widest text-[#f5c542]/60 mb-1.5 whitespace-nowrap"
                                      x-text="h2h.title"></span>
                                <div class="flex gap-1">
                                    <template x-for="slot in [{label:'Home',o:h2h.home},{label:'Draw',o:h2h.draw},{label:'Away',o:h2h.away}]" :key="slot.label">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="text-[7px] uppercase text-gray-600 w-11 text-center truncate" x-text="slot.label"></span>
                                            <template x-if="slot.o">
                                                <button
                                                    @click.stop="$store.betSlip.add(event.id, event.id+'_h2h', slot.o.name, slot.o.price, event.home_team, event.away_team, 'h2h', h2h.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                                    :class="$store.betSlip.isSelected(event.id, slot.o.name, 'h2h') ? 'bg-[#f5c542] border-[#f5c542] text-black' : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                    class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                                    x-text="parseFloat(slot.o.price).toFixed(2)"
                                                ></button>
                                            </template>
                                            <template x-if="!slot.o">
                                                <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Spreads Group --}}
                        <template x-if="sp.home || sp.away">
                            <div class="flex flex-col items-center px-2 border-l border-[#1e1e1e]">
                                <span class="text-[8px] font-bold uppercase tracking-widest text-[#f5c542]/60 mb-1.5 whitespace-nowrap"
                                      x-text="sp.title"></span>
                                <div class="flex gap-1">
                                    <template x-for="slot in [{label:'Home',o:sp.home},{label:'Away',o:sp.away}]" :key="slot.label">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="text-[7px] uppercase text-gray-600 w-11 text-center" x-text="slot.label"></span>
                                            <template x-if="slot.o">
                                                <button
                                                    @click.stop="$store.betSlip.add(event.id, event.id+'_spreads', slot.o.name+' '+$store.sportsbook.pt(slot.o.point), slot.o.price, event.home_team, event.away_team, 'spreads', sp.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                                    :class="$store.betSlip.isSelected(event.id, slot.o.name+' '+$store.sportsbook.pt(slot.o.point), 'spreads') ? 'bg-[#f5c542] border-[#f5c542] text-black' : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                    class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                                    x-text="parseFloat(slot.o.price).toFixed(2)"
                                                ></button>
                                            </template>
                                            <template x-if="!slot.o">
                                                <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Totals Group --}}
                        <template x-if="tot.over || tot.under">
                            <div class="flex flex-col items-center px-2 border-l border-[#1e1e1e]">
                                <span class="text-[8px] font-bold uppercase tracking-widest text-[#f5c542]/60 mb-1.5 whitespace-nowrap"
                                      x-text="tot.title"></span>
                                <div class="flex gap-1">
                                    <template x-for="slot in [{label:'Over',o:tot.over},{label:'Under',o:tot.under}]" :key="slot.label">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="text-[7px] uppercase text-gray-600 w-11 text-center" x-text="slot.label"></span>
                                            <template x-if="slot.o">
                                                <button
                                                    @click.stop="$store.betSlip.add(event.id, event.id+'_totals', slot.label+' '+$store.sportsbook.pt(slot.o.point), slot.o.price, event.home_team, event.away_team, 'totals', tot.title, event.commence_time, $store.sportsbook.isLive(event.commence_time))"
                                                    :class="$store.betSlip.isSelected(event.id, slot.label+' '+$store.sportsbook.pt(slot.o.point), 'totals') ? 'bg-[#f5c542] border-[#f5c542] text-black' : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                    class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                                    x-text="parseFloat(slot.o.price).toFixed(2)"
                                                ></button>
                                            </template>
                                            <template x-if="!slot.o">
                                                <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- More markets chevron --}}
                        <div class="flex items-center pl-3 border-l border-[#1e1e1e] ml-1">
                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>

                    </div>
                </div>

            </div>
        </template>

        {{-- Empty state --}}
        <div x-show="$store.sportsbook.loaded && $store.sportsbook.getEvents().length === 0"
             class="flex flex-col items-center justify-center py-20 text-gray-600">
            <div class="text-4xl mb-3">📭</div>
            <div class="text-sm">No events available for this sport.</div>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- MARKETS MODAL (Alpine-driven, always in DOM)        --}}
    {{-- ════════════════════════════════════════════════════ --}}

    {{-- Backdrop --}}
    <div
        x-show="$store.sportsbook.modalOpen"
        x-cloak
        @click="$store.sportsbook.closeModal()"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    {{-- Panel --}}
    <div
        x-show="$store.sportsbook.modalOpen"
        x-cloak
        class="fixed z-50 bg-[#111] border-l border-[#222] flex flex-col
               bottom-0 left-0 right-0 max-h-[85vh] rounded-t-2xl
               md:bottom-0 md:top-0 md:left-auto md:right-0 md:w-[420px]
               md:max-h-none md:rounded-none md:rounded-l-xl md:border-t-0"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 md:translate-y-0 md:translate-x-8"
        x-transition:enter-end="opacity-100 translate-y-0 md:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 md:translate-x-0"
        x-transition:leave-end="opacity-0 translate-y-8 md:translate-y-0 md:translate-x-8"
    >
        {{-- Modal Header --}}
        <div class="flex-shrink-0 px-4 pt-4 pb-3 border-b border-[#222]">

            <div class="md:hidden flex justify-center mb-3">
                <div class="w-10 h-1 rounded-full bg-[#333]"></div>
            </div>

            <template x-if="$store.sportsbook.modalEvent">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-white font-bold text-base leading-snug">
                                <span x-text="$store.sportsbook.modalEvent.home_team"></span>
                                <span class="text-gray-500 font-normal text-sm mx-1">vs</span>
                                <span x-text="$store.sportsbook.modalEvent.away_team"></span>
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] text-gray-500"
                                      x-text="$store.sportsbook.formatModalTime($store.sportsbook.modalEvent.commence_time)"></span>
                                <span x-show="$store.sportsbook.isLive($store.sportsbook.modalEvent.commence_time)"
                                      class="text-[9px] font-bold text-red-400 animate-pulse">● LIVE</span>
                                <span x-show="!$store.sportsbook.isLive($store.sportsbook.modalEvent.commence_time)"
                                      class="text-[9px] text-gray-600 uppercase tracking-wide">Upcoming</span>
                            </div>
                        </div>
                        <button
                            @click="$store.sportsbook.closeModal()"
                            class="flex-shrink-0 w-8 h-8 rounded-full bg-[#1a1a1a] border border-[#333] flex items-center justify-center text-gray-400 hover:text-white hover:border-[#555] transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Market Category Tabs --}}
                    <div class="flex gap-1.5 overflow-x-auto mt-3 pb-1 scrollbar-none">
                        <template x-for="tab in $store.sportsbook.modalTabs()" :key="tab">
                            <button
                                @click="$store.sportsbook.setModalTab(tab)"
                                :class="$store.sportsbook.modalActiveTab === tab
                                    ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                    : 'bg-[#1a1a1a] border-[#333] text-gray-400 hover:border-[#555] hover:text-gray-200'"
                                class="flex-shrink-0 text-[10px] font-bold uppercase tracking-wide px-3 py-1.5 rounded-full border transition-all duration-100"
                                x-text="$store.sportsbook.modalTabLabel(tab)"
                            ></button>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Modal Body --}}
        <div class="flex-1 overflow-y-auto">

            {{-- Initial loading (no markets yet) --}}
            <div x-show="$store.sportsbook.modalLoadingMore && Object.keys($store.sportsbook.modalMarkets).length === 0"
                 class="flex items-center justify-center py-16">
                <div class="text-[#f5c542] text-sm animate-pulse">Loading markets...</div>
            </div>

            {{-- Markets list --}}
            <template x-for="[marketKey, marketData] in $store.sportsbook.modalMarketsFiltered()" :key="marketKey">
                <div class="pt-4 pb-2">
                    <div class="flex items-center gap-2 mx-4 mb-3">
                        <div class="flex-1 h-px bg-[#222]"></div>
                        <span class="text-[10px] font-bold text-[#f5c542] uppercase tracking-widest whitespace-nowrap px-2"
                              x-text="$store.sportsbook.marketLabel(marketKey, marketData.title)"></span>
                        <div class="flex-1 h-px bg-[#222]"></div>
                    </div>
                    <div :class="(marketData.outcomes ?? []).length <= 2 ? 'grid-cols-2' : 'grid-cols-3'"
                         class="grid gap-2 px-4">
                        <template x-for="outcome in (marketData.outcomes ?? [])" :key="outcome.name + (outcome.point ?? '') + (outcome.description ?? '')">
                            <template x-if="outcome.price > 0">
                                <button
                                    @click="$store.sportsbook.addModalToBetSlip(outcome, marketKey, marketData.title)"
                                    :class="$store.sportsbook.isModalOutcomeSelected(outcome, marketKey)
                                        ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                        : 'bg-[#1a1a1a] border-[#2a2a2a] text-white hover:border-[#f5c542]/50 hover:bg-[#f5c542]/5'"
                                    class="flex items-center justify-between px-3 py-2.5 rounded-lg border transition-colors duration-75 cursor-pointer text-left w-full"
                                >
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-medium truncate"
                                             :class="$store.sportsbook.isModalOutcomeSelected(outcome, marketKey) ? 'text-black' : 'text-gray-200'"
                                             x-text="outcome.name"></div>
                                        <div x-show="outcome.description"
                                             class="text-[10px] truncate mt-0.5"
                                             :class="$store.sportsbook.isModalOutcomeSelected(outcome, marketKey) ? 'text-black/70' : 'text-gray-500'"
                                             x-text="outcome.description"></div>
                                        <div x-show="outcome.point != null"
                                             class="text-[10px] mt-0.5"
                                             :class="$store.sportsbook.isModalOutcomeSelected(outcome, marketKey) ? 'text-black/70' : 'text-gray-500'"
                                             x-text="$store.sportsbook.pt(outcome.point)"></div>
                                    </div>
                                    <span class="font-black text-sm ml-2 flex-shrink-0"
                                          :class="$store.sportsbook.isModalOutcomeSelected(outcome, marketKey) ? 'text-black' : 'text-[#f5c542]'"
                                          x-text="parseFloat(outcome.price).toFixed(2)"></span>
                                </button>
                            </template>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Loading more spinner --}}
            <div x-show="$store.sportsbook.modalLoadingMore && Object.keys($store.sportsbook.modalMarkets).length > 0"
                 class="px-4 py-5 flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-gray-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-xs text-gray-500 animate-pulse">Loading more markets...</span>
            </div>

            {{-- No markets --}}
            <div x-show="!$store.sportsbook.modalLoadingMore && Object.keys($store.sportsbook.modalMarkets).length === 0"
                 class="flex flex-col items-center justify-center py-16 text-gray-600">
                <div class="text-3xl mb-2">📭</div>
                <div class="text-sm">No markets available for this event.</div>
            </div>

            <div class="h-6"></div>
        </div>
    </div>

</div>
