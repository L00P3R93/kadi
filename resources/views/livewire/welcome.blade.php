<div>
    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden bg-[#0a0a0a] min-h-[520px] md:min-h-[560px] flex items-center">

        {{-- Gold coin — game's signature visual asset, right side --}}
        <div class="absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-[5%] lg:right-[10%] w-[380px] md:w-[320px] lg:w-[380px] pointer-events-none select-none z-[1] opacity-10 md:opacity-100"
             aria-hidden="true">
            <img src="{{ asset('images/logo-1.png') }}" alt=""
                 width="450" height="450"
                 class="w-full h-auto object-contain"
                 style="filter: drop-shadow(0 0 40px rgba(245,197,66,0.3)) drop-shadow(0 20px 40px rgba(0,0,0,0.5));"
                 fetchpriority="high" decoding="async" />
        </div>

        {{-- Content --}}
        <div class="relative z-10 w-full max-w-7xl mx-auto px-5 sm:px-6 md:px-10 py-16 md:py-20">
            <div class="max-w-xl text-center md:text-left mx-auto md:mx-0">

                {{-- Live indicator — minimal, game-authentic --}}
                @if($livePlayers > 0)
                <div class="inline-flex items-center gap-2 mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#f5c542] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#f5c542]"></span>
                    </span>
                    <span class="font-cinzel text-[11px] font-medium text-white/60 tracking-wide">
                        Live Now · {{ number_format($livePlayers) }} Players
                    </span>
                </div>
                @endif

                {{-- Headline — Cinzel, strong hierarchy --}}
                <h1 class="font-cinzel font-black text-[2rem] sm:text-[2.5rem] md:text-[3rem] lg:text-[3.5rem] leading-[1.05] tracking-tight mb-4">
                    <span class="block text-white" style="text-shadow: 0 0 30px rgba(245,197,66,0.35);">Where Fortune</span>
                    <span class="block text-[#f5c542]" style="text-shadow: 0 0 30px rgba(245,197,66,0.35);">Favors the Bold</span>
                </h1>

                {{-- Supporting copy — concise, product-focused --}}
                <p class="text-white/50 text-[15px] md:text-base leading-relaxed mb-8 max-w-md mx-auto md:mx-0">
                    Play competitive Kadi anytime, anywhere. Enter tournaments, challenge skilled opponents, and master the game.
                </p>

                {{-- CTA — gold used sparingly, game-authentic --}}
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                    <a href="{{ auth()->check() ? $playKadiUrl : route('register') }}"
                       @auth target="_blank" rel="noopener noreferrer" @endauth
                       class="font-cinzel inline-flex items-center gap-2.5
                              rounded-full px-7 py-3.5 text-sm font-bold
                              bg-[#f5c542] text-[#0a0a0a]
                              hover:bg-[#ffde74]
                              hover:-translate-y-0.5 transition-all duration-300">
                        @auth
                            Play Kadi
                        @else
                            Sign Up Free to Play
                        @endauth
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>

                {{-- Trust indicators — simplified, game-authentic minimal style --}}
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-5 gap-y-2 mt-8">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/40" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                        </svg>
                        <span class="font-cinzel text-[11px] text-white/40">Secure &amp; Licensed</span>
                    </div>
                    <div class="w-px h-3 bg-white/10"></div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/40" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18c-1.69 0-3.267-.507-4.575-1.375l-.325-.2-2.875.855.855-2.875-.2-.325A7.963 7.963 0 0 1 4 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/>
                        </svg>
                        <span class="font-cinzel text-[11px] text-white/40">Deposits via M-Pesa</span>
                    </div>
                    <div class="w-px h-3 bg-white/10"></div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/40" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                        <span class="font-cinzel text-[11px] text-white/40">Phone &amp; Email Support</span>
                    </div>
                </div>
            </div>

            {{-- Prize Pool — clean, game-authentic styling --}}
            @php
                $seed = (int) date('YmdH');
                mt_srand($seed);
                $prizes = [
                    ['rank'=>1,'label'=>'1st',  'amount'=> 110452931 + mt_rand(-500000,500000), 'color'=>'#FFD700'],
                    ['rank'=>2,'label'=>'2nd',  'amount'=>  25016384 + mt_rand(-200000,200000), 'color'=>'#C0C0C0'],
                    ['rank'=>3,'label'=>'3rd',  'amount'=>   9978624 + mt_rand(-100000,100000), 'color'=>'#CD7F32'],
                    ['rank'=>4,'label'=>'4th',  'amount'=>   3107899 + mt_rand(-50000, 50000),  'color'=>'#60a5fa'],
                ];
            @endphp

            <div class="hidden md:block mt-10 max-w-lg"
                 x-data="{
                     prizes: @js($prizes),
                     displayed: [0,0,0,0],
                     started: false,
                     startCounting() {
                         if (this.started) return;
                         this.started = true;
                         this.prizes.forEach((prize, i) => {
                             const target = prize.amount;
                             const steps = 60;
                             let step = 0;
                             const iv = setInterval(() => {
                                 step++;
                                 const eased = 1 - Math.pow(1 - step/steps, 3);
                                 this.displayed[i] = Math.round(target * eased);
                                 this.displayed = [...this.displayed];
                                 if (step >= steps) { clearInterval(iv); this.displayed[i] = target; this.displayed = [...this.displayed]; }
                             }, 2000 / steps);
                         });
                     },
                     fmt(n) { return Math.round(n).toLocaleString(); }
                 }"
                 x-intersect.once="startCounting()">

                <div class="flex items-center gap-2 mb-3">
                    <span class="font-cinzel text-[10px] text-white/30 uppercase tracking-[0.25em] font-semibold">Today's Jackpot</span>
                    <div class="flex-1 h-px bg-white/5"></div>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    @foreach($prizes as $i => $prize)
                        <div class="text-center">
                            <div class="text-[10px] uppercase tracking-wider mb-1 font-medium" style="color: {{ $prize['color'] }}; opacity: 0.6;">
                                {{ $prize['label'] }}
                            </div>
                            <div class="font-bold text-xs md:text-sm leading-none"
                                 style="color: {{ $prize['color'] }};"
                                 :x-text="'KES ' + fmt(displayed[{{ $i }}])"
                                 x-text="'KES {{ number_format($prize['amount']) }}'">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Mobile prize strip — horizontal scroll --}}
            <div class="md:hidden mt-8 -mx-5 px-5 py-3 bg-white/[0.02] border-t border-b border-white/[0.04]"
                 x-data="{
                     prizes: @js($prizes),
                     displayed: [0,0,0,0],
                     started: false,
                     startCounting() {
                         if (this.started) return;
                         this.started = true;
                         this.prizes.forEach((prize, i) => {
                             const target = prize.amount;
                             const steps = 60;
                             let step = 0;
                             const iv = setInterval(() => {
                                 step++;
                                 const eased = 1 - Math.pow(1 - step/steps, 3);
                                 this.displayed[i] = Math.round(target * eased);
                                 this.displayed = [...this.displayed];
                                 if (step >= steps) { clearInterval(iv); this.displayed[i] = target; this.displayed = [...this.displayed]; }
                             }, 2000 / steps);
                         });
                     },
                     fmt(n) { return Math.round(n).toLocaleString(); }
                 }"
                 x-intersect.once="startCounting()">

                <div class="flex items-center justify-center gap-2 mb-3">
                    <span class="font-cinzel text-[10px] text-white/30 uppercase tracking-[0.25em] font-semibold">Jackpot</span>
                    <div class="w-8 h-px bg-white/5"></div>
                </div>

                <div class="flex justify-center gap-4">
                    @foreach($prizes as $i => $prize)
                        <div class="text-center">
                            <div class="text-[9px] uppercase tracking-wider mb-1 font-medium" style="color: {{ $prize['color'] }}; opacity: 0.6;">
                                {{ $prize['label'] }}
                            </div>
                            <div class="font-cinzel font-bold text-[11px] leading-none"
                                 style="color: {{ $prize['color'] }};"
                                 :x-text="'KES ' + fmt(displayed[{{ $i }}])"
                                 x-text="'KES {{ number_format($prize['amount']) }}'">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </section>

    {{-- ===================== FEATURED GAMES (commented out: Kadi-only focus) =====================
    <section id="games" class="py-10 bg-[#0a0a0a]">
        @php
            $featuredGames = app(\App\Services\GamesService::class)->all()->take(10)->values()->toArray();
        @endphp

        <x-carousel
            title="Featured Casino Games"
            subtitle="Top picks from our casino floor"
            view-all-link="{{ route('guest.games') }}"
        >
            @foreach($featuredGames as $game)
                <div class="group relative rounded-2xl overflow-hidden cursor-pointer
                            flex-shrink-0 w-48 sm:w-56 h-64 bg-[#111]
                            border border-[#222] hover:border-[#f5c542]/40 transition-all duration-300
                            snap-start shadow-lg hover:shadow-[0_8px_30px_rgba(245,197,66,0.12)]">
                    <img src="{{ asset($game['path']) }}" alt="{{ $game['name'] }}"
                         width="{{ $game['width'] }}" height="{{ $game['height'] }}"
                         class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                         fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                         decoding="async" />
                    <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/90 to-transparent pt-12 pb-4 px-4">
                        <a href="#" class="inline-flex items-center gap-1 text-[11px] font-bold text-black bg-[#f5c542] hover:bg-[#ffde74] px-2.5 py-1.5 rounded">Play Now</a>
                    </div>
                </div>
            @endforeach
        </x-carousel>
    </section>
    --}}

    {{-- ===================== POPULAR GAMES (commented out: Kadi-only focus) =====================
    <section id="popular" class="py-10 bg-[#0d0d0d]">
        @php
            $popularGames = app(\App\Services\GamesService::class)->all()->slice(-10)->values()->toArray();
        @endphp

        <x-carousel
            title="Popular Casino Games"
            subtitle="Popular picks from our casino floor"
            view-all-link="{{ route('guest.games') }}"
        >
            @foreach($popularGames as $game)
                <div class="group relative rounded-2xl overflow-hidden cursor-pointer
                            flex-shrink-0 w-48 sm:w-56 h-64 bg-[#111]
                            border border-[#222] hover:border-[#f5c542]/40 transition-all duration-300
                            snap-start shadow-lg hover:shadow-[0_8px_30px_rgba(245,197,66,0.12)]">
                    <img src="{{ asset($game['path']) }}" alt="{{ $game['name'] }}"
                         width="{{ $game['width'] }}" height="{{ $game['height'] }}"
                         class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                         fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                         decoding="async" />
                    <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/90 to-transparent pt-12 pb-4 px-4">
                        <a href="#" class="inline-flex items-center gap-1 text-[11px] font-bold text-black bg-[#f5c542] hover:bg-[#ffde74] px-2.5 py-1.5 rounded">Play Now</a>
                    </div>
                </div>
            @endforeach
        </x-carousel>
    </section>
    --}}

    {{-- ===================== SPORTS BETTING (commented out: Kadi-only focus) =====================
    <section class="py-10 bg-[#0a0a0a]">
        @php
            $sportsbookCards = [
                ['league' => 'Premier League', 'time' => 'Today 20:00',    'home' => 'Manchester City', 'away' => 'Arsenal',       'odds' => ['2.10', '3.40', '3.20']],
                ['league' => 'La Liga',        'time' => 'Tomorrow 21:00', 'home' => 'Barcelona',       'away' => 'Real Madrid',   'odds' => ['2.50', '3.10', '2.80']],
                ['league' => 'NBA',            'time' => 'Today 02:30',    'home' => 'LA Lakers',       'away' => 'Boston Celtics','odds' => ['1.85', '1.95']],
                ['league' => 'Serie A',        'time' => 'Sat 19:45',      'home' => 'AC Milan',        'away' => 'Juventus',      'odds' => ['2.30', '3.20', '2.90']],
                ['league' => 'Bundesliga',     'time' => 'Sat 17:30',      'home' => 'Bayern Munich',   'away' => 'Dortmund',      'odds' => ['1.75', '3.80', '4.50']],
            ];
        @endphp

        <x-carousel
            title="Sports Betting"
            subtitle="Live odds — updated every 2 hours"
            view-all-link="{{ route('sportsbook') }}"
            view-all-text="Full Sportsbook"
        >
            @foreach($sportsbookCards as $card)
                <div class="flex-shrink-0 w-64 snap-start bg-[#111] border border-[#222] rounded-xl p-4 hover:border-[#f5c542]/40 transition-all duration-200">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] text-[#f5c542] font-bold uppercase tracking-wide bg-[#1a1200] px-2 py-0.5 rounded">{{ $card['league'] }}</span>
                        <span class="text-[10px] text-gray-500">{{ $card['time'] }}</span>
                    </div>
                    <div class="text-center mb-3">
                        <div class="text-white font-bold text-sm">{{ $card['home'] }}</div>
                        <div class="text-gray-600 text-xs my-1">vs</div>
                        <div class="text-white font-bold text-sm">{{ $card['away'] }}</div>
                    </div>
                    <a href="{{ route('sportsbook') }}" class="block w-full text-center bg-[#f5c542] text-black font-bold py-2 rounded hover:bg-[#ffde74] transition text-xs">Bet Now</a>
                </div>
            @endforeach
            <div class="flex-shrink-0 w-48 snap-start flex flex-col items-center justify-center bg-[#111] border border-dashed border-[#333] rounded-xl p-6 text-center">
                <div class="text-3xl mb-2">🏆</div>
                <div class="text-gray-400 text-sm font-semibold mb-3">More Sports</div>
                <a href="{{ route('sportsbook') }}" class="text-xs font-bold text-black bg-[#f5c542] hover:bg-[#ffde74] px-4 py-2 rounded transition">View All</a>
            </div>
        </x-carousel>
    </section>
    --}}

    {{-- ===================== WHY CHOOSE US =====================
    <section id="about" class="py-24" style="background-color:#111111;background-image:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(245,197,66,0.03) 40px,rgba(245,197,66,0.03) 41px);">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mb-16 text-center">
                <h2 class="text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">WHY CHOOSE US</h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                @php
                    $features = [
                        ['icon' => '🔒', 'title' => 'Secure & Licensed',  'desc' => '256-bit SSL encryption. Fully licensed and regulated.'],
                        ['icon' => '⚡', 'title' => 'Instant Payouts',    'desc' => 'Withdraw your winnings within minutes, not days.'],
                        ['icon' => '🎁', 'title' => 'Daily Bonuses',      'desc' => 'New rewards, free spins, and cashback every single day.'],
                    ];
                @endphp

                @foreach ($features as $feature)
                    <div class="glass-card glass-card-hover p-8 text-center">
                        <div class="mb-6 text-5xl">{{ $feature['icon'] }}</div>
                        <h3 class="mb-3 text-xl font-semibold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">{{ $feature['title'] }}</h3>
                        <p class="text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    --}}

    {{-- ===================== PROMOTIONS BANNER =====================
    <section id="promotions" class="py-20" style="background: linear-gradient(135deg, #1a1000, #2a1f00, #1a1000);">
        <div class="mx-auto max-w-7xl px-6">
            <div class="glass-card flex flex-col items-start gap-6 border-l-4 border-[#f5c542] p-8 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="mb-3 inline-flex rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-3 py-1 text-xs tracking-widest text-[#f5c542]">
                        🎁 WELCOME OFFER
                    </div>
                    <h2 class="mb-2 text-3xl font-bold text-[#f5c542] md:text-4xl" style="font-family: 'Cinzel', serif;">
                        Get 250 Free Coins Instantly
                    </h2>
                    <p class="text-[#f5f5f0]/60" style="font-family: 'Outfit', sans-serif;">Earn bonus coins by watching ads and playing mini-games. T&Cs apply.</p>
                </div>
                @auth
                    <a href="{{ $playKadiUrl }}" wire:navigate
                       class="btn-casino-primary shrink-0 inline-block rounded-full px-8 py-4 no-underline">
                        Play Kadi →
                    </a>
                @else
                    <a href="{{ route('register') }}" wire:navigate
                       class="btn-casino-primary shrink-0 inline-block rounded-full px-8 py-4 no-underline">
                        Sign Up →
                    </a>
                @endauth

            </div>
        </div>
    </section>
    --}}

    {{-- ===================== STATS BAR ===================== --}}
    <section class="border-y border-[#f5c542]/30 bg-black py-12">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                @php

                    $stats = [
                        ['value' => (string) (6000 + $users), 'label' => 'Community Members'],
                        ['value' => '10',    'label' => 'Game Modes'],
                        ['value' => '250',    'label' => 'Free Coins on Signup'],
                        ['value' => '24/7',   'label' => 'Support'],
                    ];
                @endphp

                @foreach ($stats as $i => $stat)
                    <div class="flex flex-col items-center gap-2 text-center {{ $i < count($stats) - 1 ? 'md:border-r md:border-[#f5c542]/20' : '' }}">
                        <div class="text-3xl font-bold text-[#f5c542] md:text-4xl" style="font-family: 'Cinzel', serif;">{{ $stat['value'] }}</div>
                        <div class="text-sm text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
