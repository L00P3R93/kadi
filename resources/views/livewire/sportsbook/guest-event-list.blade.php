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

                // Get all market odds
                $h2h      = $this->getEventOdds($event['id']);
                $spreads  = $this->getEventSpreads($event['id']);
                $totals   = $this->getEventTotals($event['id']);

                $h2hOdds    = $h2h['outcomes'] ?? [];
                $h2hTitle   = $h2h['title'] ?? '3 Way';
                $spreadOdds = $spreads['spreads'] ?? [];
                $spreadTitle = $spreads['title'] ?? 'Handicap';
                $totalOdds  = $totals['results'] ?? [];
                $totalTitle = $totals['title'] ?? 'Over/Under';

                // Build H2H slots
                $qHome = null; $qDraw = null; $qAway = null;
                foreach ($h2hOdds as $o) {
                    if (strtolower($o['name']) === 'draw') $qDraw = $o;
                    elseif (!$qHome) $qHome = $o;
                    else $qAway = $o;
                }
                $h2hSlots = [
                    ['label' => 'Home', 'outcome' => $qHome],
                    ['label' => 'Draw', 'outcome' => $qDraw],
                    ['label' => 'Away', 'outcome' => $qAway],
                ];

                // Build Spread slots
                $spreadHome = null; $spreadAway = null;
                foreach ($spreadOdds as $o) {
                    if ($o['name'] === $event['home_team']) $spreadHome = $o;
                    elseif ($o['name'] === $event['away_team']) $spreadAway = $o;
                }

                // Build Total slots
                $totalOver = null; $totalUnder = null;
                foreach ($totalOdds as $o) {
                    if (stripos($o['name'], 'over') !== false) $totalOver = $o;
                    elseif (stripos($o['name'], 'under') !== false) $totalUnder = $o;
                }
            @endphp

            <div class="border-b border-[#1a1a1a]">

                {{-- ══ MOBILE LAYOUT (block below md) ══ --}}
                <div class="md:hidden">

                    {{-- Meta + Teams header --}}
                    <div class="px-3 pt-2.5 pb-2">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-[9px] bg-[#222] text-[#f5c542] px-1.5 py-0.5 rounded font-bold uppercase tracking-wide truncate max-w-[90px]">
                                {{ $event['sport_title'] ?? '' }}
                            </span>
                            <span class="text-[10px] text-gray-500">{{ $timeLabel }}</span>
                            @if($isLive)
                                <span class="bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded animate-pulse">LIVE</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-white font-semibold text-sm flex-1 truncate">{{ $event['home_team'] }}</span>
                            <span class="text-gray-600 text-[10px] font-bold flex-shrink-0 px-1">vs</span>
                            <span class="text-white font-semibold text-sm flex-1 truncate text-right">{{ $event['away_team'] }}</span>
                        </div>
                    </div>

                    {{-- H2H Market Section --}}
                    @if($qHome || $qDraw || $qAway)
                        <div class="mx-3 mb-1.5 rounded overflow-hidden border border-[#222]">
                            <div class="bg-[#0d0d0d] px-2.5 py-1 border-b border-[#222]">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400">{{ $h2hTitle }}</span>
                            </div>
                            <div class="flex">
                                @foreach($h2hSlots as $slot)
                                    @if($slot['outcome'])
                                        @php
                                            $mTeam    = $slot['outcome']['name'];
                                            $mPrice   = (float) $slot['outcome']['price'];
                                            $mSlipKey = $event['id'] . '_h2h';
                                            $mShort   = strlen($mTeam) > 12 ? substr($mTeam, 0, 11) . '…' : $mTeam;
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
                                                '{{ addslashes($h2hTitle) }}',
                                                '{{ $event['commence_time'] }}',
                                                {{ $isLiveStr }}
                                            )"
                                            :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($mTeam) }}', 'h2h')
                                                ? 'bg-[#f5c542] text-black border-[#f5c542]'
                                                : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                            class="flex flex-col items-center flex-1 py-2 border-r border-[#222] last:border-r-0 transition-colors duration-75 cursor-pointer"
                                        >
                                            <span class="text-[8px] font-semibold leading-tight text-center px-0.5 truncate w-full text-center"
                                                  :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($mTeam) }}', 'h2h') ? 'text-black/70' : 'text-gray-400'">
                                                {{ strtoupper($mShort) }}
                                            </span>
                                            <span class="text-sm font-black leading-tight mt-0.5">{{ number_format($mPrice, 2) }}</span>
                                        </button>
                                    @else
                                        <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222] last:border-r-0">
                                            <span class="text-[8px] text-gray-700">{{ strtoupper($slot['label']) }}</span>
                                            <span class="text-sm text-gray-700 mt-0.5">—</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Spreads Market Section --}}
                    @if($spreadHome || $spreadAway)
                        <div class="mx-3 mb-1.5 rounded overflow-hidden border border-[#222]">
                            <div class="bg-[#0d0d0d] px-2.5 py-1 border-b border-[#222]">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400">{{ $spreadTitle }}</span>
                            </div>
                            <div class="flex">
                                @php $sSlipKey = $event['id'] . '_spreads'; @endphp

                                @if($spreadHome)
                                    @php $spPoint = $spreadHome['point'] > 0 ? '+' . $spreadHome['point'] : $spreadHome['point']; @endphp
                                    <button
                                        @click.stop="$store.betSlip.add(
                                            '{{ $event['id'] }}',
                                            '{{ $sSlipKey }}',
                                            '{{ addslashes($spreadHome['name']) }} {{ $spPoint }}',
                                            {{ (float)$spreadHome['price'] }},
                                            '{{ addslashes($event['home_team']) }}',
                                            '{{ addslashes($event['away_team']) }}',
                                            'spreads',
                                            '{{ addslashes($spreadTitle) }}',
                                            '{{ $event['commence_time'] }}',
                                            {{ $isLiveStr }}
                                        )"
                                        :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($spreadHome['name']) }} {{ $spPoint }}', 'spreads')
                                            ? 'bg-[#f5c542] text-black border-[#f5c542]'
                                            : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 border-r border-[#222] transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold text-gray-400 leading-tight"
                                              :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($spreadHome['name']) }} {{ $spPoint }}', 'spreads') ? 'text-black/70' : ''">
                                            HOME {{ $spPoint }}
                                        </span>
                                        <span class="text-sm font-black leading-tight mt-0.5">{{ number_format($spreadHome['price'], 2) }}</span>
                                    </button>
                                @else
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222]">
                                        <span class="text-[8px] text-gray-700">HOME</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                @endif

                                @if($spreadAway)
                                    @php $spPoint = $spreadAway['point'] > 0 ? '+' . $spreadAway['point'] : $spreadAway['point']; @endphp
                                    <button
                                        @click.stop="$store.betSlip.add(
                                            '{{ $event['id'] }}',
                                            '{{ $sSlipKey }}',
                                            '{{ addslashes($spreadAway['name']) }} {{ $spPoint }}',
                                            {{ (float)$spreadAway['price'] }},
                                            '{{ addslashes($event['home_team']) }}',
                                            '{{ addslashes($event['away_team']) }}',
                                            'spreads',
                                            '{{ addslashes($spreadTitle) }}',
                                            '{{ $event['commence_time'] }}',
                                            {{ $isLiveStr }}
                                        )"
                                        :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($spreadAway['name']) }} {{ $spPoint }}', 'spreads')
                                            ? 'bg-[#f5c542] text-black border-[#f5c542]'
                                            : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold text-gray-400 leading-tight"
                                              :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($spreadAway['name']) }} {{ $spPoint }}', 'spreads') ? 'text-black/70' : ''">
                                            AWAY {{ $spPoint }}
                                        </span>
                                        <span class="text-sm font-black leading-tight mt-0.5">{{ number_format($spreadAway['price'], 2) }}</span>
                                    </button>
                                @else
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111]">
                                        <span class="text-[8px] text-gray-700">AWAY</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Totals Market Section + More Markets --}}
                    @if($totalOver || $totalUnder)
                        <div class="mx-3 mb-2.5 rounded overflow-hidden border border-[#222]">
                            <div class="bg-[#0d0d0d] px-2.5 py-1 border-b border-[#222]">
                                <span class="text-[9px] font-bold uppercase tracking-widest text-gray-400">{{ $totalTitle }}</span>
                            </div>
                            <div class="flex">
                                @php $tSlipKey = $event['id'] . '_totals'; @endphp

                                @if($totalOver)
                                    @php $tPoint = $totalOver['point'] ?? ''; @endphp
                                    <button
                                        @click.stop="$store.betSlip.add(
                                            '{{ $event['id'] }}',
                                            '{{ $tSlipKey }}',
                                            'Over {{ $tPoint }}',
                                            {{ (float)$totalOver['price'] }},
                                            '{{ addslashes($event['home_team']) }}',
                                            '{{ addslashes($event['away_team']) }}',
                                            'totals',
                                            '{{ addslashes($totalTitle) }}',
                                            '{{ $event['commence_time'] }}',
                                            {{ $isLiveStr }}
                                        )"
                                        :class="$store.betSlip.isSelected('{{ $event['id'] }}', 'Over {{ $tPoint }}', 'totals')
                                            ? 'bg-[#f5c542] text-black border-[#f5c542]'
                                            : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 border-r border-[#222] transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold text-gray-400 leading-tight"
                                              :class="$store.betSlip.isSelected('{{ $event['id'] }}', 'Over {{ $tPoint }}', 'totals') ? 'text-black/70' : ''">
                                            OVER {{ $tPoint }}
                                        </span>
                                        <span class="text-sm font-black leading-tight mt-0.5">{{ number_format($totalOver['price'], 2) }}</span>
                                    </button>
                                @else
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222]">
                                        <span class="text-[8px] text-gray-700">OVER</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                @endif

                                @if($totalUnder)
                                    @php $tPoint = $totalUnder['point'] ?? ''; @endphp
                                    <button
                                        @click.stop="$store.betSlip.add(
                                            '{{ $event['id'] }}',
                                            '{{ $tSlipKey }}',
                                            'Under {{ $tPoint }}',
                                            {{ (float)$totalUnder['price'] }},
                                            '{{ addslashes($event['home_team']) }}',
                                            '{{ addslashes($event['away_team']) }}',
                                            'totals',
                                            '{{ addslashes($totalTitle) }}',
                                            '{{ $event['commence_time'] }}',
                                            {{ $isLiveStr }}
                                        )"
                                        :class="$store.betSlip.isSelected('{{ $event['id'] }}', 'Under {{ $tPoint }}', 'totals')
                                            ? 'bg-[#f5c542] text-black border-[#f5c542]'
                                            : 'bg-[#111] text-white hover:bg-[#1e1e2e]'"
                                        class="flex flex-col items-center flex-1 py-2 border-r border-[#222] transition-colors duration-75 cursor-pointer"
                                    >
                                        <span class="text-[8px] font-semibold text-gray-400 leading-tight"
                                              :class="$store.betSlip.isSelected('{{ $event['id'] }}', 'Under {{ $tPoint }}', 'totals') ? 'text-black/70' : ''">
                                            UNDER {{ $tPoint }}
                                        </span>
                                        <span class="text-sm font-black leading-tight mt-0.5">{{ number_format($totalUnder['price'], 2) }}</span>
                                    </button>
                                @else
                                    <div class="flex flex-col items-center flex-1 py-2 bg-[#111] border-r border-[#222]">
                                        <span class="text-[8px] text-gray-700">UNDER</span>
                                        <span class="text-sm text-gray-700 mt-0.5">—</span>
                                    </div>
                                @endif

                                {{-- More markets chevron --}}
                                <button
                                    wire:click.stop="openMarketsModal('{{ $event['id'] }}')"
                                    class="flex-shrink-0 w-10 bg-[#0d0d0d] flex items-center justify-center
                                           text-gray-500 hover:text-[#f5c542] hover:bg-[#111] transition-colors"
                                    title="More markets"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- No totals — standalone more markets button --}}
                        <div class="flex justify-end px-3 pb-2.5">
                            <button
                                wire:click.stop="openMarketsModal('{{ $event['id'] }}')"
                                class="flex items-center gap-1 text-[10px] text-gray-500 hover:text-[#f5c542]
                                       bg-[#0d0d0d] border border-[#222] rounded px-2.5 py-1.5 transition-colors"
                            >
                                More markets
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                </div>

                {{-- ══ DESKTOP LAYOUT (hidden below md, shown md+) ══ --}}
                <div class="hidden md:flex items-stretch px-4 py-3 gap-3 hover:bg-[#0f0f0f] transition-colors cursor-pointer"
                     wire:click="openMarketsModal('{{ $event['id'] }}')">

                    {{-- LEFT: Match info --}}
                    <div class="flex-shrink-0 w-44 flex flex-col justify-center">
                        <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                            <span class="text-[9px] bg-[#222] text-[#f5c542] px-1.5 py-0.5 rounded font-bold uppercase tracking-wide truncate max-w-[90px]">
                                {{ $event['sport_title'] ?? '' }}
                            </span>
                            <span class="text-[9px] text-gray-500">{{ $timeLabel }}</span>
                            @if($isLive)
                                <span class="bg-red-600 text-white text-[8px] font-bold px-1 py-0.5 rounded animate-pulse">LIVE</span>
                            @endif
                        </div>
                        <div class="text-white font-semibold text-xs leading-snug truncate">{{ $event['home_team'] }}</div>
                        <div class="text-gray-600 text-[9px] my-0.5">vs</div>
                        <div class="text-white font-semibold text-xs leading-snug truncate">{{ $event['away_team'] }}</div>
                    </div>

                    {{-- RIGHT: Market groups --}}
                    <div class="flex items-center flex-1 justify-end gap-0">

                        {{-- H2H Group --}}
                        @if($qHome || $qDraw || $qAway)
                            <div class="flex flex-col items-center px-2 border-l border-[#1e1e1e]">
                                <span class="text-[8px] font-bold uppercase tracking-widest text-[#f5c542]/60 mb-1.5 whitespace-nowrap">
                                    {{ $h2hTitle }}
                                </span>
                                <div class="flex gap-1">
                                    @foreach($h2hSlots as $slot)
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span class="text-[7px] uppercase text-gray-600 w-11 text-center truncate">{{ $slot['label'] }}</span>
                                            @if($slot['outcome'])
                                                @php
                                                    $mTeam    = $slot['outcome']['name'];
                                                    $mPrice   = (float)$slot['outcome']['price'];
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
                                                        '{{ addslashes($h2hTitle) }}',
                                                        '{{ $event['commence_time'] }}',
                                                        {{ $isLiveStr }}
                                                    )"
                                                    :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($mTeam) }}', 'h2h')
                                                        ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                                        : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                    class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                                >
                                                    {{ number_format($mPrice, 2) }}
                                                </button>
                                            @else
                                                <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Spreads Group --}}
                        @if($spreadHome || $spreadAway)
                            <div class="flex flex-col items-center px-2 border-l border-[#1e1e1e]">
                                <span class="text-[8px] font-bold uppercase tracking-widest text-[#f5c542]/60 mb-1.5 whitespace-nowrap">
                                    {{ $spreadTitle }}
                                </span>
                                <div class="flex gap-1">
                                    @php $sSlipKey = $event['id'] . '_spreads'; @endphp

                                    {{-- Home spread --}}
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-[7px] uppercase text-gray-600 w-11 text-center">Home</span>
                                        @if($spreadHome)
                                            @php $spPoint = $spreadHome['point'] > 0 ? '+' . $spreadHome['point'] : $spreadHome['point']; @endphp
                                            <button
                                                @click.stop="$store.betSlip.add(
                                                    '{{ $event['id'] }}',
                                                    '{{ $sSlipKey }}',
                                                    '{{ addslashes($spreadHome['name']) }} {{ $spPoint }}',
                                                    {{ (float)$spreadHome['price'] }},
                                                    '{{ addslashes($event['home_team']) }}',
                                                    '{{ addslashes($event['away_team']) }}',
                                                    'spreads',
                                                    '{{ addslashes($spreadTitle) }}',
                                                    '{{ $event['commence_time'] }}',
                                                    {{ $isLiveStr }}
                                                )"
                                                :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($spreadHome['name']) }} {{ $spPoint }}', 'spreads')
                                                    ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                                    : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                            >
                                                {{ number_format($spreadHome['price'], 2) }}
                                            </button>
                                        @else
                                            <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                        @endif
                                    </div>

                                    {{-- Away spread --}}
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-[7px] uppercase text-gray-600 w-11 text-center">Away</span>
                                        @if($spreadAway)
                                            @php $spPoint = $spreadAway['point'] > 0 ? '+' . $spreadAway['point'] : $spreadAway['point']; @endphp
                                            <button
                                                @click.stop="$store.betSlip.add(
                                                    '{{ $event['id'] }}',
                                                    '{{ $sSlipKey }}',
                                                    '{{ addslashes($spreadAway['name']) }} {{ $spPoint }}',
                                                    {{ (float)$spreadAway['price'] }},
                                                    '{{ addslashes($event['home_team']) }}',
                                                    '{{ addslashes($event['away_team']) }}',
                                                    'spreads',
                                                    '{{ addslashes($spreadTitle) }}',
                                                    '{{ $event['commence_time'] }}',
                                                    {{ $isLiveStr }}
                                                )"
                                                :class="$store.betSlip.isSelected('{{ $event['id'] }}', '{{ addslashes($spreadAway['name']) }} {{ $spPoint }}', 'spreads')
                                                    ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                                    : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                            >
                                                {{ number_format($spreadAway['price'], 2) }}
                                            </button>
                                        @else
                                            <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Totals Group --}}
                        @if($totalOver || $totalUnder)
                            <div class="flex flex-col items-center px-2 border-l border-[#1e1e1e]">
                                <span class="text-[8px] font-bold uppercase tracking-widest text-[#f5c542]/60 mb-1.5 whitespace-nowrap">
                                    {{ $totalTitle }}
                                </span>
                                <div class="flex gap-1">
                                    @php $tSlipKey = $event['id'] . '_totals'; @endphp

                                    {{-- Over --}}
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-[7px] uppercase text-gray-600 w-11 text-center">Over</span>
                                        @if($totalOver)
                                            @php $tPoint = $totalOver['point'] ?? ''; @endphp
                                            <button
                                                @click.stop="$store.betSlip.add(
                                                    '{{ $event['id'] }}',
                                                    '{{ $tSlipKey }}',
                                                    'Over {{ $tPoint }}',
                                                    {{ (float)$totalOver['price'] }},
                                                    '{{ addslashes($event['home_team']) }}',
                                                    '{{ addslashes($event['away_team']) }}',
                                                    'totals',
                                                    '{{ addslashes($totalTitle) }}',
                                                    '{{ $event['commence_time'] }}',
                                                    {{ $isLiveStr }}
                                                )"
                                                :class="$store.betSlip.isSelected('{{ $event['id'] }}', 'Over {{ $tPoint }}', 'totals')
                                                    ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                                    : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                            >
                                                {{ number_format($totalOver['price'], 2) }}
                                            </button>
                                        @else
                                            <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                        @endif
                                    </div>

                                    {{-- Under --}}
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="text-[7px] uppercase text-gray-600 w-11 text-center">Under</span>
                                        @if($totalUnder)
                                            @php $tPoint = $totalUnder['point'] ?? ''; @endphp
                                            <button
                                                @click.stop="$store.betSlip.add(
                                                    '{{ $event['id'] }}',
                                                    '{{ $tSlipKey }}',
                                                    'Under {{ $tPoint }}',
                                                    {{ (float)$totalUnder['price'] }},
                                                    '{{ addslashes($event['home_team']) }}',
                                                    '{{ addslashes($event['away_team']) }}',
                                                    'totals',
                                                    '{{ addslashes($totalTitle) }}',
                                                    '{{ $event['commence_time'] }}',
                                                    {{ $isLiveStr }}
                                                )"
                                                :class="$store.betSlip.isSelected('{{ $event['id'] }}', 'Under {{ $tPoint }}', 'totals')
                                                    ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                                    : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                                class="w-11 py-1.5 rounded border text-sm font-black text-center transition-colors duration-75 cursor-pointer"
                                            >
                                                {{ number_format($totalUnder['price'], 2) }}
                                            </button>
                                        @else
                                            <div class="w-11 py-1.5 rounded border border-[#222] bg-[#0d0d0d] text-center text-xs text-gray-700">—</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- More markets chevron --}}
                        <div class="flex items-center pl-3 border-l border-[#1e1e1e] ml-1">
                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>

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
                            $marketTitle = $marketData['title'] ?? \App\Support\SportsbookMarkets::getLabel($marketKey);
                        @endphp

                        @if($showInTab && count($outcomes) > 0)
                            <div class="pt-4 pb-2">
                                {{-- Market heading with title from data --}}
                                <div class="flex items-center gap-2 mx-4 mb-3">
                                    <div class="flex-1 h-px bg-[#222]"></div>
                                    <span class="text-[10px] font-bold text-[#f5c542] uppercase tracking-widest whitespace-nowrap px-2">
                                        {{ $marketTitle }}
                                    </span>
                                    <div class="flex-1 h-px bg-[#222]"></div>
                                </div>

                                {{-- Outcomes grid --}}
                                <div class="grid grid-cols-{{ count($outcomes) <= 2 ? '2' : '3' }} gap-2 px-4">
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
                                                    '{{ addslashes($marketTitle) }}',
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
