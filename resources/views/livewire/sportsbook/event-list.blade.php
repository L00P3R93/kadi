<div wire:poll.60000ms="refreshData" class="flex flex-col h-full">

    {{-- Top Bar --}}
    <div class="sticky top-0 z-10 bg-[#111] border-b border-[#222] px-4 py-3 flex justify-between items-center flex-shrink-0">
        <span class="text-white font-bold">
            {{ ucwords(str_replace('_', ' ', $sport)) }}
        </span>
        <div class="flex items-center gap-3">
            @php $quota = cache('odds_api.quota'); @endphp
            @if($quota)
                <span class="text-[10px] text-gray-700">
                    {{ number_format($quota['remaining']) }} credits left
                </span>
            @endif
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

    {{-- Loading overlay --}}
    <div wire:loading.flex class="flex-1 items-center justify-center py-20">
        <div class="text-[#f5c542] text-sm animate-pulse">Loading odds...</div>
    </div>

    {{-- Event list --}}
    <div wire:loading.remove class="flex-1 overflow-y-auto">
        @forelse($events as $event)
            @php
                $dt = \Carbon\Carbon::parse($event['commence_time'])->setTimezone('Africa/Nairobi');
                $isLive = $dt->isPast();
                if ($dt->isToday()) $timeLabel = 'Today ' . $dt->format('H:i');
                elseif ($dt->isTomorrow()) $timeLabel = 'Tomorrow ' . $dt->format('H:i');
                else $timeLabel = $dt->format('D d/m H:i');

                $quickOdds = $this->getEventOdds($event['id']);
                $qHome = null; $qDraw = null; $qAway = null;
                foreach ($quickOdds as $o) {
                    $name = strtolower($o['name'] ?? '');
                    if ($name === 'draw') $qDraw = $o;
                    elseif (!$qHome) $qHome = $o;
                    else $qAway = $o;
                }
                $quickSlots = [
                    ['label' => '1', 'outcome' => $qHome],
                    ['label' => 'X', 'outcome' => $qDraw],
                    ['label' => '2', 'outcome' => $qAway],
                ];
                $isExpanded = $expandedEventId === $event['id'];
                $isLiveStr  = $isLive ? 'true' : 'false';
            @endphp

            <div class="border-b border-[#1a1a1a]">

                {{-- EVENT ROW (clickable header) --}}
                <div
                    wire:click="expandEvent('{{ $event['id'] }}')"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-[#0f0f0f] transition cursor-pointer {{ $isExpanded ? 'bg-[#0f0f0f]' : '' }}"
                >
                    {{-- LEFT: meta --}}
                    <div class="w-28 flex-shrink-0">
                        <span class="text-xs bg-[#222] text-[#f5c542] px-2 py-0.5 rounded font-semibold truncate block max-w-[110px]">
                            {{ $event['sport_title'] ?? '' }}
                        </span>
                        <div class="text-[10px] text-gray-500 mt-0.5">{{ $timeLabel }}</div>
                        @if($isLive)
                            <span class="inline-block mt-1 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded animate-pulse">LIVE</span>
                        @endif
                    </div>

                    {{-- CENTER: Teams --}}
                    <div class="flex-1 min-w-0">
                        <div class="text-white font-semibold text-sm truncate">{{ $event['home_team'] }}</div>
                        <div class="text-gray-600 text-[10px] my-0.5">vs</div>
                        <div class="text-white font-semibold text-sm truncate">{{ $event['away_team'] }}</div>
                    </div>

                    {{-- RIGHT: h2h quick odds + chevron --}}
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @foreach($quickSlots as $slot)
                            @if($slot['outcome'])
                                @php
                                    $team  = $slot['outcome']['name'];
                                    $price = (float) $slot['outcome']['price'];
                                @endphp
                                <button
                                    @click.stop="$store.betSlip.add(
                                        '{{ $event['id'] }}',
                                        '{{ $event['id'] }}_h2h',
                                        '{{ addslashes($team) }}',
                                        {{ $price }},
                                        '{{ addslashes($event['home_team']) }}',
                                        '{{ addslashes($event['away_team']) }}',
                                        'h2h',
                                        'Match Winner',
                                        '{{ $event['commence_time'] }}',
                                        {{ $isLiveStr }}
                                    )"
                                    :class="$store.betSlip.isSelected('{{ $event['id'] }}_h2h', '{{ addslashes($team) }}')
                                        ? 'bg-[#f5c542] border-[#f5c542] text-black font-bold'
                                        : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black'"
                                    class="flex flex-col items-center min-w-[44px] px-1.5 py-1 rounded border transition-colors duration-75 cursor-pointer"
                                >
                                    <span class="text-[9px] opacity-60">{{ $slot['label'] }}</span>
                                    <span class="font-bold text-xs">{{ number_format($price, 2) }}</span>
                                </button>
                            @else
                                <div class="flex flex-col items-center min-w-[44px] px-1.5 py-1 rounded border border-[#222] bg-[#111]">
                                    <span class="text-[9px] text-gray-700">{{ $slot['label'] }}</span>
                                    <span class="text-xs text-gray-700">—</span>
                                </div>
                            @endif
                        @endforeach

                        <svg class="{{ $isExpanded ? 'rotate-180' : '' }} w-3 h-3 text-gray-600 ml-1.5 transition-transform duration-200 flex-shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- EXPANDABLE MARKETS PANEL --}}
                @if($isExpanded)
                    <div class="bg-[#0d0d0d] border-t border-[#1a1a1a] px-4 pb-4">

                        {{-- Inline loading indicator while expandEvent is running --}}
                        <div wire:loading.flex wire:target="expandEvent" class="items-center justify-center py-4">
                            <div class="text-[#f5c542] text-xs animate-pulse">Loading markets...</div>
                        </div>

                        <div wire:loading.remove wire:target="expandEvent">
                            @if($loadingMarkets)
                                <div class="py-4 text-center">
                                    <div class="text-[#f5c542] text-xs animate-pulse">Loading markets...</div>
                                </div>

                            @elseif(count($eventMarkets) > 0)

                                {{-- Market tabs --}}
                                <div class="flex gap-1.5 overflow-x-auto py-3 scrollbar-none">
                                    @foreach($eventMarkets as $marketKey)
                                        <button
                                            wire:click.stop="selectMarket('{{ $marketKey }}')"
                                            class="flex-shrink-0 text-[10px] font-semibold px-2.5 py-1 rounded border transition whitespace-nowrap
                                                {{ $activeMarket === $marketKey
                                                    ? 'bg-[#f5c542] text-black border-[#f5c542]'
                                                    : 'bg-[#111] text-gray-400 border-[#333] hover:border-[#555] hover:text-gray-200' }}"
                                        >
                                            {{ \App\Support\SportsbookMarkets::getLabel($marketKey) }}
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Outcomes for active market --}}
                                @php $outcomes = $this->getOutcomesForActiveMarket(); @endphp

                                @if(count($outcomes) > 0)
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach($outcomes as $outcome)
                                            @php
                                                $outcomeName        = $outcome['name'] ?? '';
                                                $outcomeDesc        = $outcome['description'] ?? '';
                                                $outcomePrice       = (float)($outcome['price'] ?? 0);
                                                $outcomePoint       = isset($outcome['point']) ? ($outcome['point'] > 0 ? '+' : '') . $outcome['point'] : null;
                                                $displayName        = $outcomeDesc ? "{$outcomeName} {$outcomeDesc}" : $outcomeName;
                                                $betKey             = $event['id'] . '_' . $activeMarket;
                                                $marketLabelEscaped = addslashes(\App\Support\SportsbookMarkets::getLabel($activeMarket));
                                            @endphp
                                            @if($outcomePrice > 0)
                                                <button
                                                    @click.stop="$store.betSlip.add(
                                                        '{{ $event['id'] }}',
                                                        '{{ $betKey }}',
                                                        '{{ addslashes($displayName) }}',
                                                        {{ $outcomePrice }},
                                                        '{{ addslashes($event['home_team']) }}',
                                                        '{{ addslashes($event['away_team']) }}',
                                                        '{{ $activeMarket }}',
                                                        '{{ $marketLabelEscaped }}',
                                                        '{{ $event['commence_time'] }}',
                                                        {{ $isLiveStr }}
                                                    )"
                                                    :class="$store.betSlip.isSelected('{{ $betKey }}', '{{ addslashes($displayName) }}')
                                                        ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                                        : 'bg-[#111] border-[#222] hover:bg-[#1a1a1a] hover:border-[#f5c542]/50 text-white'"
                                                    class="flex items-center justify-between px-3 py-2 rounded border text-left transition-colors duration-75 cursor-pointer"
                                                >
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-xs truncate">
                                                            {{ $outcomeName }}
                                                            @if($outcomeDesc)
                                                                <span class="text-[10px] text-gray-500">{{ $outcomeDesc }}</span>
                                                            @endif
                                                        </div>
                                                        @if($outcomePoint)
                                                            <div class="text-[10px] text-gray-500">{{ $outcomePoint }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm font-bold ml-2 flex-shrink-0 text-[#f5c542]">
                                                        {{ number_format($outcomePrice, 2) }}
                                                    </div>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-gray-600 text-xs py-3">No outcomes available for this market.</div>
                                @endif

                            @else
                                <div class="text-gray-600 text-xs py-3">No markets available for this event.</div>
                            @endif
                        </div>

                    </div>
                @endif

            </div>

        @empty
            <div class="flex flex-col items-center justify-center py-20 text-gray-600">
                <div class="text-4xl mb-3">📭</div>
                <div class="text-sm">No events available for this sport.</div>
            </div>
        @endforelse
    </div>

</div>
