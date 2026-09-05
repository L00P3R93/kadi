<div x-data="{ activeTab: 'single' }">

    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden bg-[#0a0a0a] min-h-[380px] md:min-h-[440px] flex items-center">

        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(245,197,66,0.08) 0%, transparent 70%);"></div>

        @php
            $casinoImages = cache()->remember('welcome_casino_bg_images', 3600, function () {
                return array_slice(glob(public_path('casino/*.{png,jpg,webp}'), GLOB_BRACE), 0, 4);
            });
            $bgPositions = [
                ['top-4 -left-8 md:top-8 md:-left-4', 'rotate-[-15deg]'],
                ['top-0 right-0 md:-right-6',          'rotate-[10deg]'],
                ['bottom-4 left-16 md:bottom-8 md:left-24', 'rotate-[8deg]'],
                ['-bottom-4 right-8 md:right-16',      'rotate-[-12deg]'],
            ];
        @endphp
        @foreach($casinoImages as $i => $imgPath)
            @php [$pos, $rot] = $bgPositions[$i] ?? ['top-0 left-0', '']; @endphp
            <img src="{{ asset('casino/' . basename($imgPath)) }}"
                 alt="" width="208" height="208"
                 class="absolute {{ $pos }} {{ $rot }} opacity-[0.08] pointer-events-none select-none w-40 md:w-52 object-contain"
                 loading="lazy" decoding="async" aria-hidden="true" />
        @endforeach

        <div class="relative z-10 w-full max-w-5xl mx-auto px-6 py-14 md:py-20 text-center">
            <div class="inline-flex items-center gap-2 bg-[#f5c542]/10 border border-[#f5c542]/20 rounded-full px-4 py-1.5 mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-[#f5c542] animate-pulse"></span>
                <span class="font-cinzel text-[10px] text-[#f5c542] uppercase tracking-[0.2em] font-semibold">Game Modes &amp; Prizes</span>
            </div>

            <h1 class="font-cinzel font-black text-3xl md:text-5xl text-[#f5c542] leading-tight tracking-wide mb-3"
                style="text-shadow: 0 0 30px rgba(245,197,66,0.35);">
                PLAY. WIN. REPEAT.
            </h1>
            <p class="text-gray-400 text-sm md:text-base leading-relaxed max-w-xl mx-auto">
                From head-to-head singles to high-stakes jackpots — pick your game, place your bet, and take home real winnings.
            </p>

            {{-- Quick nav --}}
            <div class="mt-8 flex flex-wrap items-center justify-center gap-2">
                @foreach ([
                    ['href' => '#single', 'label' => 'Single Games'],
                    ['href' => '#tournaments', 'label' => 'Tournaments'],
                    ['href' => '#jackpots', 'label' => 'Jackpots'],
                ] as $link)
                    <a href="{{ $link['href'] }}"
                       class="rounded-full border border-[#f5c542]/20 bg-white/[0.03] px-4 py-1.5 text-xs text-[#f5f5f0]/70 transition hover:border-[#f5c542]/50 hover:text-[#f5c542] hover:bg-[#f5c542]/5">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== SINGLE GAMES ===================== --}}
    <section id="single" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Head to Head</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Single Games</h2>
                <p class="mt-3 text-sm text-[#6b6b6b] max-w-xl mx-auto">Challenge a single opponent. Pick your stake, win big. The more players at the table, the higher the payout.</p>
            </div>

            {{-- Mode selector pills --}}
            <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
                <button @click="activeTab = 'single2'"
                        :class="activeTab === 'single2' ? 'border-[#f5c542] bg-[#f5c542]/10 text-[#f5c542] shadow-[0_0_16px_rgba(245,197,66,0.2)]' : 'border-[#f5c542]/15 text-[#f5f5f0]/60'"
                        class="flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-semibold transition-all duration-200">
                    <span class="text-lg">⚔️</span> vs 2
                </button>
                <button @click="activeTab = 'single3'"
                        :class="activeTab === 'single3' ? 'border-[#f5c542] bg-[#f5c542]/10 text-[#f5c542] shadow-[0_0_16px_rgba(245,197,66,0.2)]' : 'border-[#f5c542]/15 text-[#f5f5f0]/60'"
                        class="flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-semibold transition-all duration-200">
                    <span class="text-lg">🗡️</span> vs 3
                </button>
                <button @click="activeTab = 'single4'"
                        :class="activeTab === 'single4' ? 'border-[#f5c542] bg-[#f5c542]/10 text-[#f5c542] shadow-[0_0_16px_rgba(245,197,66,0.2)]' : 'border-[#f5c542]/15 text-[#f5f5f0]/60'"
                        class="flex items-center gap-2 rounded-full border px-5 py-2.5 text-sm font-semibold transition-all duration-200">
                    <span class="text-lg">🏰</span> vs 4
                </button>
            </div>

            {{-- vs 2 --}}
            <div x-show="activeTab === 'single2'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass-card overflow-hidden border-l-4 !border-l-[#f5c542]">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-[#f5c542]/10">
                        <span class="text-2xl">⚔️</span>
                        <div>
                            <h3 class="font-cinzel font-bold text-[#f5c542] text-sm uppercase tracking-wide">2-Player Match</h3>
                            <p class="text-xs text-[#6b6b6b]">Winner takes all — 1.9× your stake</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#f5c542]/10">
                                    <th class="px-6 py-3 text-left font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.2em]">Stake</th>
                                    <th class="px-6 py-3 text-right font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.2em]">Winner Gets</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $vs2 = [
                                        ['stake' => 20, 'win' => 38],
                                        ['stake' => 50, 'win' => 95],
                                        ['stake' => 100, 'win' => 190],
                                        ['stake' => 250, 'win' => 475],
                                        ['stake' => 500, 'win' => 950],
                                        ['stake' => 1000, 'win' => 1900],
                                    ];
                                @endphp
                                @foreach($vs2 as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#f5c542]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-6 py-3.5 text-[#f5f5f0]/80 font-semibold">KSh {{ number_format($row['stake']) }}</td>
                                        <td class="px-6 py-3.5 text-right font-cinzel font-bold text-[#f5c542]">KSh {{ number_format($row['win']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- vs 3 --}}
            <div x-show="activeTab === 'single3'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass-card overflow-hidden border-l-4 !border-l-[#60a5fa]">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-[#60a5fa]/10">
                        <span class="text-2xl">🗡️</span>
                        <div>
                            <h3 class="font-cinzel font-bold text-[#60a5fa] text-sm uppercase tracking-wide">3-Player Match</h3>
                            <p class="text-xs text-[#6b6b6b]">Outplay two rivals — 2.85× your stake</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#60a5fa]/10">
                                    <th class="px-6 py-3 text-left font-cinzel text-[10px] text-[#60a5fa]/60 uppercase tracking-[0.2em]">Stake</th>
                                    <th class="px-6 py-3 text-right font-cinzel text-[10px] text-[#60a5fa]/60 uppercase tracking-[0.2em]">Winner Gets</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $vs3 = [
                                        ['stake' => 20, 'win' => '57.00'],
                                        ['stake' => 50, 'win' => '142.50'],
                                        ['stake' => 100, 'win' => '285.00'],
                                        ['stake' => 250, 'win' => '712.50'],
                                        ['stake' => 500, 'win' => '1,425.00'],
                                        ['stake' => 1000, 'win' => '2,850.00'],
                                    ];
                                @endphp
                                @foreach($vs3 as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#60a5fa]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-6 py-3.5 text-[#f5f5f0]/80 font-semibold">KSh {{ number_format($row['stake']) }}</td>
                                        <td class="px-6 py-3.5 text-right font-cinzel font-bold text-[#60a5fa]">KSh {{ $row['win'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- vs 4 --}}
            <div x-show="activeTab === 'single4'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="glass-card overflow-hidden border-l-4 !border-l-[#c084fc]">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-[#c084fc]/10">
                        <span class="text-2xl">🏰</span>
                        <div>
                            <h3 class="font-cinzel font-bold text-[#c084fc] text-sm uppercase tracking-wide">4-Player Match</h3>
                            <p class="text-xs text-[#6b6b6b]">Dominate the table — 3.8× your stake</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#c084fc]/10">
                                    <th class="px-6 py-3 text-left font-cinzel text-[10px] text-[#c084fc]/60 uppercase tracking-[0.2em]">Stake</th>
                                    <th class="px-6 py-3 text-right font-cinzel text-[10px] text-[#c084fc]/60 uppercase tracking-[0.2em]">Winner Gets</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $vs4 = [
                                        ['stake' => 20, 'win' => 76],
                                        ['stake' => 50, 'win' => 190],
                                        ['stake' => 100, 'win' => 380],
                                        ['stake' => 250, 'win' => 950],
                                        ['stake' => 500, 'win' => 1900],
                                        ['stake' => 1000, 'win' => 3800],
                                    ];
                                @endphp
                                @foreach($vs4 as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#c084fc]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-6 py-3.5 text-[#f5f5f0]/80 font-semibold">KSh {{ number_format($row['stake']) }}</td>
                                        <td class="px-6 py-3.5 text-right font-cinzel font-bold text-[#c084fc]">KSh {{ number_format($row['win']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== TOURNAMENTS ===================== --}}
    <section id="tournaments" class="scroll-mt-24 py-16 md:py-20" style="background-color:#111111;background-image:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(245,197,66,0.03) 40px,rgba(245,197,66,0.03) 41px);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Elimination Brackets</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Tournaments</h2>
                <p class="mt-3 text-sm text-[#6b6b6b] max-w-xl mx-auto">Battle through multiple rounds of elimination. The deeper you go, the bigger the multiplier — up to ×28.8.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                {{-- 3 Rounds --}}
                <div class="glass-card overflow-hidden border-t-4 !border-t-[#60a5fa] group hover:shadow-[0_0_30px_rgba(96,165,250,0.12)] transition-all duration-300">
                    <div class="px-6 py-5 text-center border-b border-white/5">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#60a5fa]/10 border border-[#60a5fa]/30 text-2xl mb-3">🥉</div>
                        <div class="font-cinzel text-[10px] text-[#60a5fa]/60 uppercase tracking-[0.25em] mb-1">Bronze Tier</div>
                        <h3 class="font-cinzel font-bold text-lg text-[#60a5fa]">3 Rounds</h3>
                        <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-[#60a5fa]/10 border border-[#60a5fa]/30 px-3 py-1">
                            <span class="font-cinzel text-xs font-bold text-[#60a5fa]">×7.2</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#60a5fa]/10">
                                    <th class="px-5 py-2.5 text-left font-cinzel text-[10px] text-[#60a5fa]/60 uppercase tracking-[0.2em]">Stake</th>
                                    <th class="px-5 py-2.5 text-right font-cinzel text-[10px] text-[#60a5fa]/60 uppercase tracking-[0.2em]">Wins</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $t3 = [
                                        ['stake' => 20, 'win' => 144],
                                        ['stake' => 50, 'win' => 360],
                                        ['stake' => 100, 'win' => 720],
                                        ['stake' => 250, 'win' => 1800],
                                        ['stake' => 500, 'win' => 3600],
                                        ['stake' => 1000, 'win' => 7200],
                                    ];
                                @endphp
                                @foreach($t3 as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#60a5fa]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-5 py-2.5 text-[#f5f5f0]/80">KSh {{ number_format($row['stake']) }}</td>
                                        <td class="px-5 py-2.5 text-right font-cinzel font-bold text-[#60a5fa]">KSh {{ number_format($row['win']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 4 Rounds --}}
                <div class="glass-card overflow-hidden border-t-4 !border-t-[#f5c542] group hover:shadow-[0_0_30px_rgba(245,197,66,0.12)] transition-all duration-300">
                    <div class="px-6 py-5 text-center border-b border-white/5">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#f5c542]/10 border border-[#f5c542]/30 text-2xl mb-3">🥈</div>
                        <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-1">Silver Tier</div>
                        <h3 class="font-cinzel font-bold text-lg text-[#f5c542]">4 Rounds</h3>
                        <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-[#f5c542]/10 border border-[#f5c542]/30 px-3 py-1">
                            <span class="font-cinzel text-xs font-bold text-[#f5c542]">×14.4</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#f5c542]/10">
                                    <th class="px-5 py-2.5 text-left font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.2em]">Stake</th>
                                    <th class="px-5 py-2.5 text-right font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.2em]">Wins</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $t4 = [
                                        ['stake' => 20, 'win' => 288],
                                        ['stake' => 50, 'win' => 720],
                                        ['stake' => 100, 'win' => 1440],
                                        ['stake' => 250, 'win' => 3600],
                                        ['stake' => 500, 'win' => 7200],
                                        ['stake' => 1000, 'win' => 14400],
                                    ];
                                @endphp
                                @foreach($t4 as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#f5c542]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-5 py-2.5 text-[#f5f5f0]/80">KSh {{ number_format($row['stake']) }}</td>
                                        <td class="px-5 py-2.5 text-right font-cinzel font-bold text-[#f5c542]">KSh {{ number_format($row['win']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 5 Rounds --}}
                <div class="glass-card overflow-hidden border-t-4 !border-t-[#ffde74] group hover:shadow-[0_0_30px_rgba(255,222,116,0.12)] transition-all duration-300">
                    <div class="px-6 py-5 text-center border-b border-white/5">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#ffde74]/10 border border-[#ffde74]/30 text-2xl mb-3">🥇</div>
                        <div class="font-cinzel text-[10px] text-[#ffde74]/60 uppercase tracking-[0.25em] mb-1">Gold Tier</div>
                        <h3 class="font-cinzel font-bold text-lg text-[#ffde74]">5 Rounds</h3>
                        <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-[#ffde74]/10 border border-[#ffde74]/30 px-3 py-1">
                            <span class="font-cinzel text-xs font-bold text-[#ffde74]">×28.8</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#ffde74]/10">
                                    <th class="px-5 py-2.5 text-left font-cinzel text-[10px] text-[#ffde74]/60 uppercase tracking-[0.2em]">Stake</th>
                                    <th class="px-5 py-2.5 text-right font-cinzel text-[10px] text-[#ffde74]/60 uppercase tracking-[0.2em]">Wins</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $t5 = [
                                        ['stake' => 20, 'win' => 576],
                                        ['stake' => 50, 'win' => 1440],
                                        ['stake' => 100, 'win' => 2880],
                                        ['stake' => 250, 'win' => 7200],
                                        ['stake' => 500, 'win' => 14400],
                                        ['stake' => 1000, 'win' => 28800],
                                    ];
                                @endphp
                                @foreach($t5 as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#ffde74]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-5 py-2.5 text-[#f5f5f0]/80">KSh {{ number_format($row['stake']) }}</td>
                                        <td class="px-5 py-2.5 text-right font-cinzel font-bold text-[#ffde74]">KSh {{ number_format($row['win']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===================== JACKPOTS ===================== --}}
    <section id="jackpots" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">The Big Leagues</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Jackpots</h2>
                <p class="mt-3 text-sm text-[#6b6b6b] max-w-xl mx-auto">Multi-table elimination brackets with massive prize pools. Entry fees fund the entire prize structure.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

                {{-- Gold JP --}}
                <div class="relative glass-card overflow-hidden border-t-4 !border-t-[#FFD700] group hover:shadow-[0_0_40px_rgba(255,215,0,0.15)] transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 pointer-events-none" style="background: radial-gradient(circle at top right, rgba(255,215,0,0.1), transparent 70%);"></div>
                    <div class="px-6 py-6 text-center border-b border-[#FFD700]/10 relative">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#FFD700]/10 border-2 border-[#FFD700]/40 text-3xl mb-3"
                             style="box-shadow: 0 0 30px rgba(255,215,0,0.2);">
                            👑
                        </div>
                        <div class="font-cinzel text-[10px] text-[#FFD700]/60 uppercase tracking-[0.25em] mb-1">Gold Jackpot</div>
                        <h3 class="font-cinzel font-black text-xl text-[#FFD700]">Gold JP</h3>
                        <div class="mt-2 flex items-center justify-center gap-3">
                            <span class="inline-flex items-center rounded-full bg-[#FFD700]/10 border border-[#FFD700]/30 px-3 py-1 text-xs font-cinzel font-bold text-[#FFD700]">KSh 100</span>
                            <span class="inline-flex items-center rounded-full bg-white/5 border border-white/10 px-3 py-1 text-xs text-[#6b6b6b]">21 Games</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#FFD700]/10">
                                    <th class="px-5 py-3 text-left font-cinzel text-[10px] text-[#FFD700]/60 uppercase tracking-[0.2em]">Category</th>
                                    <th class="px-5 py-3 text-right font-cinzel text-[10px] text-[#FFD700]/60 uppercase tracking-[0.2em]">Total Prize</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $goldJp = [
                                        ['emoji' => '🥇', 'label' => '1st Place',    'prize' => '110,452,969'],
                                        ['emoji' => '🥈', 'label' => 'Runner-Up',    'prize' => '25,016,339'],
                                        ['emoji' => '🥉', 'label' => 'Semis (2)',    'prize' => '9,978,584'],
                                        ['emoji' => '🎯', 'label' => 'Quarters (4)', 'prize' => '3,086,378'],
                                    ];
                                @endphp
                                @foreach($goldJp as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#FFD700]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-5 py-3">
                                            <span class="mr-2">{{ $row['emoji'] }}</span>
                                            <span class="text-[#f5f5f0]/80">{{ $row['label'] }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right font-cinzel font-bold text-[#FFD700]">KSh {{ $row['prize'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-[#FFD700]/5">
                                    <td class="px-5 py-3 font-cinzel font-bold text-[#FFD700]">TOTAL</td>
                                    <td class="px-5 py-3 text-right font-cinzel font-black text-[#FFD700] text-base">KSh 167,772,160</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Bronze JP --}}
                <div class="relative glass-card overflow-hidden border-t-4 !border-t-[#CD7F32] group hover:shadow-[0_0_40px_rgba(205,127,50,0.15)] transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 pointer-events-none" style="background: radial-gradient(circle at top right, rgba(205,127,50,0.1), transparent 70%);"></div>
                    <div class="px-6 py-6 text-center border-b border-[#CD7F32]/10 relative">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#CD7F32]/10 border-2 border-[#CD7F32]/40 text-3xl mb-3"
                             style="box-shadow: 0 0 30px rgba(205,127,50,0.2);">
                            🏆
                        </div>
                        <div class="font-cinzel text-[10px] text-[#CD7F32]/60 uppercase tracking-[0.25em] mb-1">Bronze Jackpot</div>
                        <h3 class="font-cinzel font-black text-xl text-[#CD7F32]">Bronze JP</h3>
                        <div class="mt-2 flex items-center justify-center gap-3">
                            <span class="inline-flex items-center rounded-full bg-[#CD7F32]/10 border border-[#CD7F32]/30 px-3 py-1 text-xs font-cinzel font-bold text-[#CD7F32]">KSh 50</span>
                            <span class="inline-flex items-center rounded-full bg-white/5 border border-white/10 px-3 py-1 text-xs text-[#6b6b6b]">17 Games</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#CD7F32]/10">
                                    <th class="px-5 py-3 text-left font-cinzel text-[10px] text-[#CD7F32]/60 uppercase tracking-[0.2em]">Category</th>
                                    <th class="px-5 py-3 text-right font-cinzel text-[10px] text-[#CD7F32]/60 uppercase tracking-[0.2em]">Total Prize</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $bronzeJp = [
                                        ['emoji' => '🥇', 'label' => '1st Place',    'prize' => '3,451,655'],
                                        ['emoji' => '🥈', 'label' => 'Runner-Up',    'prize' => '781,760'],
                                        ['emoji' => '🥉', 'label' => 'Semis (2)',    'prize' => '311,830'],
                                        ['emoji' => '🎯', 'label' => 'Quarters (4)', 'prize' => '96,449'],
                                    ];
                                @endphp
                                @foreach($bronzeJp as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#CD7F32]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-5 py-3">
                                            <span class="mr-2">{{ $row['emoji'] }}</span>
                                            <span class="text-[#f5f5f0]/80">{{ $row['label'] }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right font-cinzel font-bold text-[#CD7F32]">KSh {{ $row['prize'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-[#CD7F32]/5">
                                    <td class="px-5 py-3 font-cinzel font-bold text-[#CD7F32]">TOTAL</td>
                                    <td class="px-5 py-3 text-right font-cinzel font-black text-[#CD7F32] text-base">KSh 5,242,871</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Silver JP --}}
                <div class="relative glass-card overflow-hidden border-t-4 !border-t-[#C0C0C0] group hover:shadow-[0_0_40px_rgba(192,192,192,0.12)] transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 pointer-events-none" style="background: radial-gradient(circle at top right, rgba(192,192,192,0.08), transparent 70%);"></div>
                    <div class="px-6 py-6 text-center border-b border-[#C0C0C0]/10 relative">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#C0C0C0]/10 border-2 border-[#C0C0C0]/40 text-3xl mb-3"
                             style="box-shadow: 0 0 30px rgba(192,192,192,0.15);">
                            🥈
                        </div>
                        <div class="font-cinzel text-[10px] text-[#C0C0C0]/60 uppercase tracking-[0.25em] mb-1">Silver Jackpot</div>
                        <h3 class="font-cinzel font-black text-xl text-[#C0C0C0]">Silver JP</h3>
                        <div class="mt-2 flex items-center justify-center gap-3">
                            <span class="inline-flex items-center rounded-full bg-[#C0C0C0]/10 border border-[#C0C0C0]/30 px-3 py-1 text-xs font-cinzel font-bold text-[#C0C0C0]">KSh 20</span>
                            <span class="inline-flex items-center rounded-full bg-white/5 border border-white/10 px-3 py-1 text-xs text-[#6b6b6b]">13 Games</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#C0C0C0]/10">
                                    <th class="px-5 py-3 text-left font-cinzel text-[10px] text-[#C0C0C0]/60 uppercase tracking-[0.2em]">Category</th>
                                    <th class="px-5 py-3 text-right font-cinzel text-[10px] text-[#C0C0C0]/60 uppercase tracking-[0.2em]">Total Prize</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $silverJp = [
                                        ['emoji' => '🥇', 'label' => '1st Place',    'prize' => '86,291'],
                                        ['emoji' => '🥈', 'label' => 'Runner-Up',    'prize' => '19,544'],
                                        ['emoji' => '🥉', 'label' => 'Semis (2)',    'prize' => '7,795'],
                                        ['emoji' => '🎯', 'label' => 'Quarters (4)', 'prize' => '2,441'],
                                    ];
                                @endphp
                                @foreach($silverJp as $row)
                                    <tr class="border-b border-white/5 hover:bg-[#C0C0C0]/5 transition-colors {{ $loop->last ? 'border-b-0' : '' }}">
                                        <td class="px-5 py-3">
                                            <span class="mr-2">{{ $row['emoji'] }}</span>
                                            <span class="text-[#f5f5f0]/80">{{ $row['label'] }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right font-cinzel font-bold text-[#C0C0C0]">KSh {{ $row['prize'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-[#C0C0C0]/5">
                                    <td class="px-5 py-3 font-cinzel font-bold text-[#C0C0C0]">TOTAL</td>
                                    <td class="px-5 py-3 text-right font-cinzel font-black text-[#C0C0C0] text-base">KSh 131,189</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ===================== CLOSING CTA ===================== --}}
    <section class="py-16 md:py-20" style="background: linear-gradient(135deg, #1a1000, #2a1f00, #1a1000);">
        <div class="mx-auto max-w-4xl px-6 text-center">
            <div class="mb-4 inline-flex rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-3 py-1 text-xs tracking-widest text-[#f5c542]">
                ♠ READY TO PLAY?
            </div>
            <h2 class="mb-3 text-3xl md:text-4xl font-bold text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                Pick Your Game. Place Your Bet.
            </h2>
            <p class="mb-8 text-[#f5f5f0]/60 max-w-lg mx-auto" style="font-family: 'Outfit', sans-serif;">
                From quick singles to massive jackpots — there's a game mode for every player and every budget.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                @auth
                    <a href="{{ route('home') }}"
                       class="btn-casino-primary inline-block rounded-full px-8 py-4 no-underline">
                        Play Kadi →
                    </a>
                @else
                    <a href="{{ route('register') }}" wire:navigate
                       class="btn-casino-primary inline-block rounded-full px-8 py-4 no-underline">
                        Create Account →
                    </a>
                    <a href="{{ route('login') }}" wire:navigate
                       class="btn-casino-ghost inline-block rounded-full px-8 py-4 no-underline">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </section>
</div>
