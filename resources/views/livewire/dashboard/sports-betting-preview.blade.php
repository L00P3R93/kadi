<div class="mb-8">
    {{-- Section header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl font-bold text-white">⚽ Sports Betting</h2>
            <p class="text-gray-500 text-xs mt-0.5">Upcoming Premier League odds</p>
        </div>
        <a href="{{ route('dashboard.sportsbook') }}" wire:navigate
           class="text-[#f5c542] text-sm font-semibold hover:underline flex items-center gap-1">
            Full Sportsbook →
        </a>
    </div>

    {{-- Events grid --}}
    @if(count($events) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($events as $event)
                @php
                    $dt = \Carbon\Carbon::parse($event['commence_time'])->setTimezone('Africa/Nairobi');
                    if ($dt->isToday()) $timeLabel = 'Today ' . $dt->format('H:i');
                    elseif ($dt->isTomorrow()) $timeLabel = 'Tomorrow ' . $dt->format('H:i');
                    else $timeLabel = $dt->format('D d M, H:i');

                    $h2h = $this->getEventOdds($event['id']);
                    $outcomes = $h2h['outcomes'] ?? [];
                    $home = null; $draw = null; $away = null;
                    foreach ($outcomes as $o) {
                        if (strtolower($o['name']) === 'draw') $draw = $o;
                        elseif (!$home) $home = $o;
                        else $away = $o;
                    }
                @endphp

                <div class="bg-[#111] border border-[#222] rounded-xl p-4 hover:border-[#f5c542]/40 transition-all duration-200">
                    {{-- Match meta --}}
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] text-[#f5c542] font-bold uppercase tracking-wide bg-[#1a1200] px-2 py-0.5 rounded">
                            Premier League
                        </span>
                        <span class="text-[10px] text-gray-500">{{ $timeLabel }}</span>
                    </div>

                    {{-- Teams --}}
                    <div class="text-center mb-3">
                        <div class="text-white font-bold text-sm">{{ $event['home_team'] }}</div>
                        <div class="text-gray-600 text-xs my-1">vs</div>
                        <div class="text-white font-bold text-sm">{{ $event['away_team'] }}</div>
                    </div>

                    {{-- Odds buttons --}}
                    <div class="grid grid-cols-3 gap-1.5 mb-3">
                        @foreach([
                            ['label' => '1', 'outcome' => $home],
                            ['label' => 'X', 'outcome' => $draw],
                            ['label' => '2', 'outcome' => $away],
                        ] as $slot)
                            @if($slot['outcome'])
                                <a href="{{ route('dashboard.sportsbook') }}" wire:navigate
                                   class="bg-[#1e1e2e] text-center py-2 rounded border border-[#2a2a3e] hover:border-[#f5c542] hover:bg-[#f5c542] hover:text-black transition group cursor-pointer">
                                    <div class="text-[10px] text-gray-500 group-hover:text-black/60">{{ $slot['label'] }}</div>
                                    <div class="text-[#f5c542] font-bold text-sm group-hover:text-black">
                                        {{ number_format((float) $slot['outcome']['price'], 2) }}
                                    </div>
                                </a>
                            @else
                                <div class="bg-[#111] text-center py-2 rounded border border-[#1a1a1a]">
                                    <div class="text-[10px] text-gray-700">{{ $slot['label'] }}</div>
                                    <div class="text-gray-700 text-sm">—</div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- CTA --}}
                    <a href="{{ route('dashboard.sportsbook') }}" wire:navigate
                       class="block w-full text-center bg-transparent border border-[#f5c542]/40 text-[#f5c542] text-xs font-bold py-1.5 rounded hover:bg-[#f5c542] hover:text-black transition">
                        Bet Now
                    </a>
                </div>
            @endforeach
        </div>
    @else
        {{-- Fallback if no events --}}
        <div class="bg-[#111] border border-[#222] rounded-xl p-8 text-center">
            <div class="text-3xl mb-2">⚽</div>
            <p class="text-gray-500 text-sm">No upcoming events right now.</p>
            <a href="{{ route('dashboard.sportsbook') }}" wire:navigate
               class="inline-block mt-3 text-[#f5c542] text-sm hover:underline">
                Browse all sports →
            </a>
        </div>
    @endif
</div>
