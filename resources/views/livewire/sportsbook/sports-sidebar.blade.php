@php
$groupIcons = [
    'Football'   => '⚽',
    'Basketball' => '🏀',
    'Boxing'     => '🥊',
];

$sportIcons = [
    'EPL'                   => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
    'La Liga'               => '🇪🇸',
    'Bundesliga'            => '🇩🇪',
    'Serie A'               => '🇮🇹',
    'Ligue 1'               => '🇫🇷',
    'UEFA Champions League' => '⭐',
    'MLS'                   => '🇺🇸',
    'NBA'                   => '🏀',
    'WNBA'                  => '🏀',
    'Boxing'                => '🥊',
];
$selectedGroup = $this->getSelectedGroup();
@endphp

<div class="h-full overflow-y-auto bg-[#111] flex flex-col">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-[#222] flex-shrink-0">
        <span class="text-[#f5c542] text-xs uppercase tracking-widest font-bold">Sports</span>
    </div>

    {{-- Groups — Alpine manages open/close state --}}
    <div
        x-data="{
            openGroups: {{ json_encode([$selectedGroup]) }},
            toggle(group) {
                if (this.openGroups.includes(group)) {
                    this.openGroups = this.openGroups.filter(g => g !== group)
                } else {
                    this.openGroups.push(group)
                }
            },
            isOpen(group) {
                return this.openGroups.includes(group)
            }
        }"
        class="flex-1 overflow-y-auto"
    >

        @foreach($sports as $group => $items)
            <div class="border-b border-[#1a1a1a]">

                {{-- Group Header (clickable toggle) --}}
                <button
                    @click="toggle('{{ $group }}')"
                    class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-[#1a1a1a] transition cursor-pointer group"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-base">{{ $groupIcons[$group] ?? '🎮' }}</span>
                        <span class="text-xs font-bold uppercase tracking-wide text-gray-300 group-hover:text-white transition">
                            {{ $group }}
                        </span>
                    </div>
                    {{-- Chevron: rotates when open --}}
                    <svg
                        :class="isOpen('{{ $group }}') ? 'rotate-180' : 'rotate-0'"
                        class="w-3 h-3 text-gray-500 transition-transform duration-200"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Collapsible sport items --}}
                <div
                    x-show="isOpen('{{ $group }}')"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                >
                    @foreach($items as $sport)
                        @php $isActive = $selectedSport === $sport['key']; @endphp
                        <button
                            wire:click="selectSport('{{ $sport['key'] }}')"
                            @click="if (!isOpen('{{ $group }}')) { openGroups.push('{{ $group }}') }"
                            class="w-full flex items-center gap-2.5 pl-8 pr-4 py-2 text-sm transition cursor-pointer border-l-2
                                {{ $isActive
                                    ? 'border-[#f5c542] text-[#f5c542] bg-[#1a1a1a] font-semibold'
                                    : 'border-transparent text-gray-400 hover:text-white hover:bg-[#161616]' }}"
                        >
                            <span class="text-xs">{{ $sportIcons[$sport['title']] ?? $groupIcons[$group] ?? '🎮' }}</span>
                            <span class="truncate text-left">{{ $sport['title'] }}</span>
                            @if($isActive)
                                <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#f5c542] flex-shrink-0"></span>
                            @endif
                        </button>
                    @endforeach
                </div>

            </div>
        @endforeach

    </div>
</div>
