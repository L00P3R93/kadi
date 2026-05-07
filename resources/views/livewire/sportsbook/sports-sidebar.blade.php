<div class="h-full overflow-y-auto bg-[#111] flex flex-col">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-[#222] flex-shrink-0">
        <span class="text-[#f5c542] text-xs uppercase tracking-widest font-bold">Sports</span>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="!$store.sportsbook.loaded" class="flex-1 px-4 py-4 space-y-3">
        <template x-for="i in [1,2,3]" :key="i">
            <div class="h-8 bg-[#1a1a1a] rounded animate-pulse"></div>
        </template>
    </div>

    {{-- Groups — Alpine manages open/close state --}}
    <div
        x-show="$store.sportsbook.loaded"
        x-data="{
            openGroups: [$store.sportsbook.getSelectedGroup()],
            toggle(group) {
                if (this.openGroups.includes(group)) {
                    this.openGroups = this.openGroups.filter(g => g !== group)
                } else {
                    this.openGroups.push(group)
                }
            },
            isOpen(group) { return this.openGroups.includes(group) }
        }"
        class="flex-1 overflow-y-auto"
    >
        <template x-for="[group, items] in Object.entries($store.sportsbook.sports)" :key="group">
            <div class="border-b border-[#1a1a1a]">

                {{-- Group Header --}}
                <button
                    @click="toggle(group)"
                    class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-[#1a1a1a] transition cursor-pointer group"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-base" x-text="$store.sportsbook.groupIcon(group)"></span>
                        <span class="text-xs font-bold uppercase tracking-wide text-gray-300 group-hover:text-white transition"
                              x-text="group"></span>
                    </div>
                    <svg
                        :class="isOpen(group) ? 'rotate-180' : 'rotate-0'"
                        class="w-3 h-3 text-gray-500 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Collapsible sport items --}}
                <div
                    x-show="isOpen(group)"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                >
                    <template x-for="sport in items" :key="sport.key">
                        <button
                            @click="$store.sportsbook.selectSport(sport.key)"
                            :class="$store.sportsbook.selectedSport === sport.key
                                ? 'border-[#f5c542] text-[#f5c542] bg-[#1a1a1a] font-semibold'
                                : 'border-transparent text-gray-400 hover:text-white hover:bg-[#161616]'"
                            class="w-full flex items-center gap-2.5 pl-8 pr-4 py-2 text-sm transition cursor-pointer border-l-2"
                        >
                            <span class="text-xs" x-text="$store.sportsbook.sportIcon(sport.title, group)"></span>
                            <span class="truncate text-left" x-text="sport.title"></span>
                            <span x-show="$store.sportsbook.selectedSport === sport.key"
                                  class="ml-auto w-1.5 h-1.5 rounded-full bg-[#f5c542] flex-shrink-0"></span>
                        </button>
                    </template>
                </div>

            </div>
        </template>
    </div>
</div>
