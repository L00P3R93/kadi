<div wire:poll.900000ms="refreshData" class="flex flex-col h-full">

    {{-- Top Bar --}}
    <div class="sticky top-0 z-10 bg-[#111] border-b border-[#222] px-4 py-3 flex justify-between items-center flex-shrink-0">
        <div class="flex items-center gap-2">
            {{-- Mobile: sidebar toggle — inherits sidenavOpen from parent Alpine scope --}}
            <button
                @click="sidenavOpen = !sidenavOpen"
                class="lg:hidden flex items-center justify-center w-7 h-7 text-gray-400 hover:text-[#f5c542] transition-colors"
                aria-label="Open sports menu"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </button>
            <span class="text-white font-bold">
                {{ ucwords(str_replace('_', ' ', $sport)) }}
            </span>
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

    {{-- Loading overlay (only for explicit sport switches, not background polls) --}}
    <div wire:loading.flex wire:target="onSportSelected" class="flex-1 items-center justify-center py-20">
        <div class="text-[#f5c542] text-sm animate-pulse">Loading odds...</div>
    </div>

    {{-- Event list --}}
    <div wire:loading.remove wire:target="onSportSelected" class="flex-1 overflow-y-auto">
        @forelse($events as $event)
            @php
                $dt = \Carbon\Carbon::parse($event['commence_time'])->setTimezone('Africa/Nairobi');
                $isLive = $dt->isPast();
                if ($dt->isToday()) $timeLabel = 'Today ' . $dt->format('H:i');
                elseif ($dt->isTomorrow()) $timeLabel = 'Tomorrow ' . $dt->format('H:i');
                else $timeLabel = $dt->format('D d/m H:i');
                $isLiveStr = $isLive ? 'true' : 'false';
            @endphp

            <div class="border-b border-[#1a1a1a] hover:bg-[#0f0f0f] transition-colors">

                {{-- ══ MOBILE LAYOUT (block below md) ══ --}}
                <div class="md:hidden px-3 py-2.5">

                    {{-- Row 1: Meta (league badge + time + live) --}}
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-[9px] bg-[#222] text-[#f5c542] px-1.5 py-0.5 rounded font-bold uppercase tracking-wide truncate max-w-[90px]">
                            {{ $event['sport_title'] ?? '' }}
                        </span>
                        <span class="text-[10px] text-gray-500">{{ $timeLabel }}</span>
                        @if($isLive)
                            <span class="bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded animate-pulse">LIVE</span>
                        @endif
                    </div>

                    {{-- Row 2: Team vs Team (FULL WIDTH, straight line) --}}
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="text-white font-semibold text-sm flex-1 truncate">{{ $event['home_team'] }}</span>
                        <span class="text-gray-600 text-[10px] font-bold flex-shrink-0 px-1">vs</span>
                        <span class="text-white font-semibold text-sm flex-1 truncate text-right">{{ $event['away_team'] }}</span>
                    </div>

                    {{-- Row 3: H2H odds buttons + chevron --}}
                    <div class="flex items-center gap-2">
                        {{-- H2H odds (1, X, 2) --}}
                        <div class="flex gap-1.5 flex-1">
                            @php
                                $quickOdds = $this->getEventOdds($event['id']);
                                $qHome = null; $qDraw = null; $qAway = null;
                                foreach ($quickOdds as $o) {
                                    if (strtolower($o['name']) === 'draw') $qDraw = $o;
                                    elseif (!$qHome) $qHome = $o;
                                    else $qAway = $o;
                                }
                                $mobileSlots = [
                                    ['label' => '1', 'outcome' => $qHome],
                                    ['label' => 'X', 'outcome' => $qDraw],
                                    ['label' => '2', 'outcome' => $qAway],
                                ];
                            @endphp

                            @foreach($mobileSlots as $slot)
                                @if($slot['outcome'])
                                    @php
                                        $mTeam    = $slot['outcome']['name'];
                                        $mPrice   = (float) $slot['outcome']['price'];
                                        $mSlipKey = $event['id'] . '_h2h';
                                    @endphp
                                    <button
                                        @click.stop="$store.betSlip.add(
                                            '{{ $event['id'] }}',
                                            '{{ $mSlipKey }}',
                                            '{{ addslashes($mTeam) }}',
                                            {{ $mPrice }},
                                            '{{ addslashes($event['home_team']) }}',
                                            '{{ addslashes($event['away_team']) }}',
                                            'h2h',
                                            'Match Winner',
                                            '{{ $event['commence_time'] }}',
                                            {{ $isLiveStr }}
                                        )"
                                        :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($mTeam) }}', 'h2h')
                                            ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                            : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                        class="flex flex-col items-center flex-1 py-1.5 rounded border transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] opacity-60 font-semibold">{{ $slot['label'] }}</span>
                                        <span class="text-xs font-bold leading-tight">{{ number_format($mPrice, 2) }}</span>
                                    </button>
                                @else
                                    <div class="flex flex-col items-center flex-1 py-1.5 rounded border border-[#1a1a1a] bg-[#111]">
                                        <span class="text-[8px] text-gray-700">{{ $slot['label'] }}</span>
                                        <span class="text-xs text-gray-700">—</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- More Markets chevron --}}
                        <button
                            wire:click.stop="openMarketsModal('{{ $event['id'] }}')"
                            class="flex-shrink-0 w-9 h-9 rounded border border-[#333] bg-[#1a1a1a]
                                   flex items-center justify-center
                                   hover:border-[#f5c542]/50 hover:text-[#f5c542] text-gray-500
                                   transition-colors duration-150"
                            title="More markets"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- ══ DESKTOP LAYOUT (hidden below md, shown md+) ══ --}}
                <div class="hidden md:flex items-center gap-3 px-4 py-3 cursor-pointer"
                     wire:click="openMarketsModal('{{ $event['id'] }}')">

                    {{-- LEFT: Meta --}}
                    <div class="w-28 flex-shrink-0">
                        <span class="text-xs bg-[#222] text-[#f5c542] px-2 py-0.5 rounded font-semibold truncate block max-w-[110px]">
                            {{ $event['sport_title'] ?? '' }}
                        </span>
                        <div class="text-[10px] text-gray-500 mt-0.5">{{ $timeLabel }}</div>
                        @if($isLive)
                            <span class="inline-block mt-1 bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded animate-pulse">LIVE</span>
                        @endif
                    </div>

                    {{-- CENTER: Teams --}}
                    <div class="flex-1 min-w-0">
                        <div class="text-white font-semibold text-sm truncate">{{ $event['home_team'] }}</div>
                        <div class="text-gray-600 text-[10px] my-0.5">vs</div>
                        <div class="text-white font-semibold text-sm truncate">{{ $event['away_team'] }}</div>
                    </div>

                    {{-- RIGHT: H2H Odds + Chevron --}}
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        @foreach($mobileSlots ?? [] as $slot)
                            @if($slot['outcome'])
                                @php
                                    $mTeam    = $slot['outcome']['name'];
                                    $mPrice   = (float) $slot['outcome']['price'];
                                    $mSlipKey = $event['id'] . '_h2h';
                                @endphp
                                <button
                                    @click.stop="$store.betSlip.add(
                                        '{{ $event['id'] }}',
                                        '{{ $mSlipKey }}',
                                        '{{ addslashes($mTeam) }}',
                                        {{ $mPrice }},
                                        '{{ addslashes($event['home_team']) }}',
                                        '{{ addslashes($event['away_team']) }}',
                                        'h2h',
                                        'Match Winner',
                                        '{{ $event['commence_time'] }}',
                                        {{ $isLiveStr }}
                                    )"
                                    :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($mTeam) }}', 'h2h')
                                        ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                        : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                    class="flex flex-col items-center min-w-[52px] px-2 py-1.5 rounded border transition-colors duration-75 cursor-pointer"
                                >
                                    <span class="text-[9px] opacity-60 font-semibold">{{ $slot['label'] }}</span>
                                    <span class="text-sm font-bold leading-tight">{{ number_format($mPrice, 2) }}</span>
                                </button>
                            @else
                                <div class="flex flex-col items-center min-w-[52px] px-2 py-1.5 rounded border border-[#222] bg-[#111]">
                                    <span class="text-[9px] text-gray-700">{{ $slot['label'] }}</span>
                                    <span class="text-sm text-gray-700">—</span>
                                </div>
                            @endif
                        @endforeach

                        <svg class="w-4 h-4 text-gray-600 ml-1 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

            </div>

        @empty
            <div class="flex flex-col items-center justify-center py-20 text-gray-600">
                <div class="text-4xl mb-3">📭</div>
                <div class="text-sm">No events available for this sport.</div>
            </div>
        @endforelse
    </div>

    {{-- ════════════════════════════════════════════════════ --}}
    {{-- SLIDE-OVER MARKETS MODAL                            --}}
    {{-- ════════════════════════════════════════════════════ --}}
    @if($showMarketsModal)

        {{-- Backdrop --}}
        <div
            wire:click="closeMarketsModal()"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
            x-data x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        ></div>

        {{-- Panel — bottom sheet on mobile, right panel on desktop --}}
        <div
            class="fixed z-50 bg-[#111] border-l border-[#222] flex flex-col
                   bottom-0 left-0 right-0 max-h-[85vh] rounded-t-2xl
                   md:bottom-0 md:top-0 md:left-auto md:right-0 md:w-[420px]
                   md:max-h-none md:rounded-none md:rounded-l-xl md:border-t-0"
            x-data
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 md:translate-y-0 md:translate-x-8"
            x-transition:enter-end="opacity-100 translate-y-0 md:translate-x-0"
        >

            {{-- ── MODAL HEADER ── --}}
            <div class="flex-shrink-0 px-4 pt-4 pb-3 border-b border-[#222]">

                {{-- Mobile drag indicator --}}
                <div class="md:hidden flex justify-center mb-3">
                    <div class="w-10 h-1 rounded-full bg-[#333]"></div>
                </div>

                @if($modalEvent)
                    @php
                        $modalDt = \Carbon\Carbon::parse($modalEvent['commence_time'] ?? now())->setTimezone('Africa/Nairobi');
                        $modalIsLive = $modalDt->isPast();
                        if ($modalDt->isToday()) $modalTimeLabel = 'Today ' . $modalDt->format('H:i');
                        elseif ($modalDt->isTomorrow()) $modalTimeLabel = 'Tomorrow ' . $modalDt->format('H:i');
                        else $modalTimeLabel = $modalDt->format('D d M, H:i');
                        $modalIsLiveStr = $modalIsLive ? 'true' : 'false';
                    @endphp

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-white font-bold text-base leading-snug">
                                {{ $modalEvent['home_team'] ?? '' }}
                                <span class="text-gray-500 font-normal text-sm mx-1">vs</span>
                                {{ $modalEvent['away_team'] ?? '' }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] text-gray-500">{{ $modalTimeLabel }}</span>
                                @if($modalIsLive)
                                    <span class="text-[9px] font-bold text-red-400 animate-pulse">● LIVE</span>
                                @else
                                    <span class="text-[9px] text-gray-600 uppercase tracking-wide">Upcoming</span>
                                @endif
                            </div>
                        </div>
                        <button
                            wire:click="closeMarketsModal()"
                            class="flex-shrink-0 w-8 h-8 rounded-full bg-[#1a1a1a] border border-[#333]
                                   flex items-center justify-center text-gray-400 hover:text-white
                                   hover:border-[#555] transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                {{-- ── MARKET CATEGORY TABS ── --}}
                @php
                    $availableTabs = ['all'];
                    foreach ($modalMarkets as $mk => $md) {
                        $cat = \App\Support\SportsbookMarkets::getTabCategory($mk);
                        if (!in_array($cat, $availableTabs)) $availableTabs[] = $cat;
                    }
                    $tabLabels = \App\Support\SportsbookMarkets::$tabLabels;
                @endphp

                <div class="flex gap-1.5 overflow-x-auto mt-3 pb-1 scrollbar-none">
                    @foreach($availableTabs as $tab)
                        <button
                            wire:click="setModalTab('{{ $tab }}')"
                            class="flex-shrink-0 text-[10px] font-bold uppercase tracking-wide
                                   px-3 py-1.5 rounded-full border transition-all duration-100
                                   {{ $modalActiveTab === $tab
                                       ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                       : 'bg-[#1a1a1a] border-[#333] text-gray-400 hover:border-[#555] hover:text-gray-200' }}"
                        >
                            {{ $tabLabels[$tab] ?? ucfirst($tab) }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- ── MODAL BODY ── --}}
            <div class="flex-1 overflow-y-auto">

                @if($modalLoading)
                    <div class="flex items-center justify-center py-16">
                        <div class="text-[#f5c542] text-sm animate-pulse">Loading markets...</div>
                    </div>

                @else
                    @foreach($modalMarkets as $marketKey => $marketData)
                        @php
                            $tabCat    = \App\Support\SportsbookMarkets::getTabCategory($marketKey);
                            $showInTab = $modalActiveTab === 'all' || $modalActiveTab === $tabCat;
                            $outcomes  = $marketData['outcomes'] ?? [];
                        @endphp

                        @if($showInTab && count($outcomes) > 0)
                            <div class="px-4 pt-4 pb-2">
                                {{-- Market heading --}}
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex-1 h-px bg-[#222]"></div>
                                    <span class="text-[10px] font-bold text-[#f5c542] uppercase tracking-widest whitespace-nowrap px-2">
                                        {{ \App\Support\SportsbookMarkets::getLabel($marketKey) }}
                                    </span>
                                    <div class="flex-1 h-px bg-[#222]"></div>
                                </div>

                                {{-- Outcomes grid --}}
                                <div class="grid grid-cols-{{ count($outcomes) <= 2 ? '2' : '3' }} gap-2">
                                    @foreach($outcomes as $outcome)
                                        @php
                                            $oName       = $outcome['name']        ?? '';
                                            $oDesc       = $outcome['description'] ?? '';
                                            $oPrice      = (float)($outcome['price'] ?? 0);
                                            $oPoint      = isset($outcome['point'])
                                                            ? ($outcome['point'] > 0 ? '+' : '') . $outcome['point']
                                                            : null;
                                            $displayName = $oDesc ? "{$oName} {$oDesc}" : $oName;
                                            $slipKey     = ($modalEventId ?? '') . '_' . $marketKey;
                                            $mktLabel    = \App\Support\SportsbookMarkets::getLabel($marketKey);
                                        @endphp

                                        @if($oPrice > 0)
                                            <button
                                                @click="$store.betSlip.add(
                                                    '{{ $modalEventId }}',
                                                    '{{ $slipKey }}',
                                                    '{{ addslashes($displayName) }}',
                                                    {{ $oPrice }},
                                                    '{{ addslashes($modalEvent['home_team'] ?? '') }}',
                                                    '{{ addslashes($modalEvent['away_team'] ?? '') }}',
                                                    '{{ $marketKey }}',
                                                    '{{ addslashes($mktLabel) }}',
                                                    '{{ $modalEvent['commence_time'] ?? '' }}',
                                                    {{ $modalIsLiveStr ?? 'false' }}
                                                )"
                                                :class="$store.betSlip.isSelected('{{ $modalEventId }}', '{{ addslashes($displayName) }}', '{{ $marketKey }}')
                                                    ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                                    : 'bg-[#1a1a1a] border-[#2a2a2a] text-white hover:border-[#f5c542]/50 hover:bg-[#f5c542]/5'"
                                                class="flex items-center justify-between px-3 py-2.5 rounded-lg border
                                                       transition-colors duration-75 cursor-pointer text-left w-full"
                                            >
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-medium truncate"
                                                         :class="$store.betSlip.isSelected('{{ $modalEventId }}', '{{ addslashes($displayName) }}', '{{ $marketKey }}') ? 'text-black' : 'text-gray-200'">
                                                        {{ $oName }}
                                                    </div>
                                                    @if($oDesc)
                                                        <div class="text-[10px] truncate mt-0.5"
                                                             :class="$store.betSlip.isSelected('{{ $modalEventId }}', '{{ addslashes($displayName) }}', '{{ $marketKey }}') ? 'text-black/70' : 'text-gray-500'">
                                                            {{ $oDesc }}
                                                        </div>
                                                    @endif
                                                    @if($oPoint)
                                                        <div class="text-[10px] mt-0.5"
                                                             :class="$store.betSlip.isSelected('{{ $modalEventId }}', '{{ addslashes($displayName) }}', '{{ $marketKey }}') ? 'text-black/70' : 'text-gray-500'">
                                                            {{ $oPoint }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="font-black text-sm ml-2 flex-shrink-0"
                                                      :class="$store.betSlip.isSelected('{{ $modalEventId }}', '{{ addslashes($displayName) }}', '{{ $marketKey }}') ? 'text-black' : 'text-[#f5c542]'">
                                                    {{ number_format($oPrice, 2) }}
                                                </span>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Loading more markets in background --}}
                    @if($modalLoadingMore)
                        <div x-data x-init="$wire.loadMoreMarkets()"
                             class="px-4 py-5 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-gray-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-xs text-gray-500 animate-pulse">Loading more markets...</span>
                        </div>
                    @elseif(empty($modalMarkets))
                        <div class="flex flex-col items-center justify-center py-16 text-gray-600">
                            <div class="text-3xl mb-2">📭</div>
                            <div class="text-sm">No markets available for this event.</div>
                        </div>
                    @endif

                    {{-- Bottom spacer for mobile bottom sheet --}}
                    <div class="h-6"></div>
                @endif

            </div>
        </div>
    @endif

</div>
