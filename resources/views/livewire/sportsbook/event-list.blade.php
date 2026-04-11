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

                $outcomes = $this->getEventOdds($event['id']);

                $home = null; $draw = null; $away = null;
                foreach ($outcomes as $o) {
                    $name = strtolower($o['name'] ?? '');
                    if ($name === 'draw') { $draw = $o; }
                    elseif (!$home) { $home = $o; }
                    else { $away = $o; }
                }

                $slots = [
                    ['label' => '1', 'outcome' => $home, 'team' => $home['name'] ?? null],
                    ['label' => 'X', 'outcome' => $draw, 'team' => 'Draw'],
                    ['label' => '2', 'outcome' => $away, 'team' => $away['name'] ?? null],
                ];
            @endphp

            <div class="px-4 py-3 border-b border-[#1a1a1a] hover:bg-[#0f0f0f] transition">

                {{-- Top meta row --}}
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs bg-[#222] text-[#f5c542] px-2 py-0.5 rounded font-semibold truncate max-w-[120px]">
                        {{ $event['sport_title'] ?? '' }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $timeLabel }}</span>
                    @if($isLive)
                        <span class="bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded animate-pulse">LIVE</span>
                    @endif
                </div>

                {{-- Teams + Odds row --}}
                <div class="flex items-center gap-3">

                    {{-- Teams --}}
                    <div class="flex-1 min-w-0">
                        <div class="text-white font-semibold text-sm truncate">{{ $event['home_team'] }}</div>
                        <div class="text-gray-500 text-xs my-0.5">vs</div>
                        <div class="text-white font-semibold text-sm truncate">{{ $event['away_team'] }}</div>
                    </div>

                    {{-- Odds buttons --}}
                    <div class="flex gap-1.5 flex-shrink-0">
                        @foreach($slots as $slot)
                            @php
                                $outcome = $slot['outcome'];
                                $price   = $outcome ? number_format((float) $outcome['price'], 2) : null;
                                $team    = $slot['team'];
                                $inSlip  = isset($betSlip[$event['id']]) && $betSlip[$event['id']]['team'] === $team;
                            @endphp

                            @if($outcome && $team)
                                <button
                                    wire:click="toggleBetSlip(
                                        '{{ $event['id'] }}',
                                        '{{ addslashes($team) }}',
                                        {{ (float) $outcome['price'] }},
                                        '{{ addslashes($event['home_team']) }}',
                                        '{{ addslashes($event['away_team']) }}'
                                    )"
                                    title="{{ $team }}"
                                    class="flex flex-col items-center min-w-[52px] px-2 py-1.5 rounded border transition-all duration-150 cursor-pointer
                                        {{ $inSlip
                                            ? 'bg-[#f5c542] border-[#f5c542] text-black'
                                            : 'bg-[#1e1e2e] border-[#2a2a3e] text-white hover:bg-[#f5c542] hover:border-[#f5c542] hover:text-black' }}"
                                >
                                    <span class="text-[10px] font-semibold opacity-70">{{ $slot['label'] }}</span>
                                    <span class="text-sm font-bold leading-tight">{{ $price }}</span>
                                </button>
                            @else
                                <div class="flex flex-col items-center min-w-[52px] px-2 py-1.5 rounded border border-[#222] bg-[#111] cursor-not-allowed">
                                    <span class="text-[10px] text-gray-700">{{ $slot['label'] }}</span>
                                    <span class="text-xs text-gray-700">—</span>
                                </div>
                            @endif
                        @endforeach
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

</div>
