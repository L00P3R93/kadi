<div>
    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden bg-[#0a0a0a] min-h-[420px] md:min-h-[480px] flex items-center">

        {{-- Radial gold glow behind left content --}}
        <div class="absolute top-1/2 left-1/4 -translate-x-1/2 -translate-y-1/2
                    w-[500px] h-[500px] rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(245,197,66,0.07) 0%, transparent 70%);">
        </div>
        {{-- Radial glow behind right content --}}
        <div class="absolute top-1/3 right-1/4 -translate-y-1/2
                    w-[400px] h-[400px] rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(245,197,66,0.05) 0%, transparent 70%);">
        </div>

        {{-- Faded background images --}}
        @php
            $casinoImages = array_slice(glob(public_path('casino/*.{png,jpg,webp}'), GLOB_BRACE), 0, 4);
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
                 alt=""
                 class="absolute {{ $pos }} {{ $rot }} opacity-[0.08] pointer-events-none select-none w-40 md:w-52 object-contain"
                 aria-hidden="true" />
        @endforeach

        {{-- Content grid --}}
        <div class="relative z-10 w-full max-w-6xl mx-auto px-6 md:px-10 py-10 md:py-14">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">

                {{-- ══ LEFT: Tagline + CTA ══ --}}
                <div class="flex flex-col items-start">

                    {{-- Live badge --}}
                    <div class="inline-flex items-center gap-2 bg-[#f5c542]/10 border border-[#f5c542]/20 rounded-full px-3 py-1 mb-5 mt-10">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#f5c542] animate-pulse"></span>
                        <span class="font-cinzel text-[10px] text-[#f5c542] uppercase tracking-[0.2em] font-semibold">
                            Live Now · 2,847 Players
                        </span>
                    </div>

                    {{-- Tagline --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-1">
                            <h1 class="font-cinzel font-bold text-2xl md:text-3xl lg:text-4xl text-[#f5c542] leading-none tracking-wide"
                                style="text-shadow: 0 0 30px rgba(245,197,66,0.35);">
                                WHERE FORTUNE
                            </h1>
                        </div>
                        <div class="flex items-center gap-3">
                            <h2 class="font-cinzel font-black text-2xl md:text-3xl lg:text-4xl text-white leading-none tracking-wide"
                                style="text-shadow: 0 2px 20px rgba(255,255,255,0.1);">
                                FAVORS THE BOLD
                            </h2>
                        </div>
                    </div>

                    {{-- Subline --}}
                    <p class="text-gray-400 text-sm md:text-base leading-relaxed mb-7 max-w-sm">
                        Premium casino games, live sports betting &amp; real-time odds.
                        Your winning moment starts here.
                    </p>

                    {{-- CTAs --}}
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('games') }}"
                           class="inline-flex items-center gap-2 bg-[#f5c542] text-black font-black
                                  px-6 py-3 rounded-xl hover:bg-[#ffde74] transition-all duration-200
                                  text-sm tracking-wide shadow-lg shadow-[#f5c542]/25
                                  hover:shadow-[#f5c542]/40 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                            </svg>
                            Play Games
                        </a>
                        <a href="{{ route('sportsbook') }}"
                           class="inline-flex items-center gap-2 bg-transparent border border-[#f5c542]/40
                                  text-[#f5c542] font-bold px-6 py-3 rounded-xl
                                  hover:border-[#f5c542] hover:bg-[#f5c542]/5 transition-all duration-200
                                  text-sm tracking-wide hover:-translate-y-0.5">
                            🏆 Sportsbook
                        </a>
                    </div>

                    {{-- Trust badges --}}
                    <div class="flex items-center gap-4 mt-5">
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <span class="text-[11px]">Secure &amp; Licensed</span>
                        </div>
                        <div class="w-px h-3 bg-gray-700"></div>
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <span class="text-[11px]">Instant Withdrawals</span>
                        </div>
                        <div class="w-px h-3 bg-gray-700"></div>
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            <span class="text-[11px]">24/7 Support</span>
                        </div>
                    </div>

                </div>

                {{-- ══ RIGHT: Prize Pool ══ --}}
                <div class="relative">

                    <div class="relative bg-gradient-to-b from-[#1a1200]/80 to-[#0a0a0a]/60
                                border border-[#f5c542]/15 rounded-2xl p-6 backdrop-blur-sm"
                         style="box-shadow: 0 0 40px rgba(245,197,66,0.06), inset 0 1px 0 rgba(245,197,66,0.1);">

                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <div class="font-cinzel text-[9px] text-[#f5c542]/50 uppercase tracking-[0.3em]">Current</div>
                                <div class="font-cinzel text-sm font-bold text-[#f5c542] tracking-wider">Prize Pool</div>
                            </div>
                            <svg class="w-8 h-8 text-[#f5c542]/20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7 2v2H2v6c0 2.21 1.79 4 4 4h.5c.8 2 2.3 3.5 4.5 3.94V20H7v2h10v-2h-4v-2.06c2.2-.44 3.7-1.94 4.5-3.94H18c2.21 0 4-1.79 4-4V4h-5V2H7zm-1 2v4H4V6h2zm12 4V4h2v4h-2z"/>
                            </svg>
                        </div>

                        <div class="h-px bg-gradient-to-r from-transparent via-[#f5c542]/20 to-transparent mb-5"></div>

                        {{-- Prize rows --}}
                        @php
                            $seed = (int) date('YmdH');
                            mt_srand($seed);
                            $prizes = [
                                ['rank'=>1,'label'=>'1st Place',      'emoji'=>'🥇','amount'=> 110452931 + mt_rand(-500000,500000), 'color'=>'#FFD700','glow'=>'rgba(255,215,0,0.4)'],
                                ['rank'=>2,'label'=>'Runner-Up',       'emoji'=>'🥈','amount'=>  25016384 + mt_rand(-200000,200000), 'color'=>'#C0C0C0','glow'=>'rgba(192,192,192,0.3)'],
                                ['rank'=>3,'label'=>'Semis (each)',    'emoji'=>'🥉','amount'=>   9978624 + mt_rand(-100000,100000), 'color'=>'#CD7F32','glow'=>'rgba(205,127,50,0.3)'],
                                ['rank'=>4,'label'=>'Quarters (each)','emoji'=>'🎯','amount'=>   3107899 + mt_rand(-50000, 50000),  'color'=>'#60a5fa','glow'=>'rgba(96,165,250,0.25)'],
                            ];
                        @endphp

                        <div
                            x-data="{
                                prizes: @js($prizes),
                                displayed: [0,0,0,0],
                                started: false,
                                startCounting() {
                                    if (this.started) return;
                                    this.started = true;
                                    this.prizes.forEach((prize, i) => {
                                        const target = prize.amount;
                                        const steps = 65;
                                        let step = 0;
                                        const iv = setInterval(() => {
                                            step++;
                                            const eased = 1 - Math.pow(1 - step/steps, 3);
                                            this.displayed[i] = Math.round(target * eased);
                                            this.displayed = [...this.displayed];
                                            if (step >= steps) { clearInterval(iv); this.displayed[i] = target; this.displayed = [...this.displayed]; }
                                        }, 2200 / steps);
                                    });
                                },
                                fmt(n) { return Math.round(n).toLocaleString(); }
                            }"
                            x-intersect.once="startCounting()"
                            class="space-y-3"
                        >
                            @foreach($prizes as $i => $prize)
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl flex-shrink-0 leading-none"
                                          style="filter: drop-shadow(0 0 8px {{ $prize['glow'] }});">
                                        {{ $prize['emoji'] }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] font-semibold uppercase tracking-widest"
                                             style="color: {{ $prize['color'] }}; opacity: 0.6;">
                                            {{ $prize['label'] }}
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <div class="font-cinzel font-black text-sm md:text-base leading-none"
                                             style="color: {{ $prize['color'] }}; text-shadow: 0 0 12px {{ $prize['glow'] }};"
                                             :x-text="'KES ' + fmt(displayed[{{ $i }}])"
                                             x-text="'KES {{ number_format($prize['amount']) }}'">
                                        </div>
                                    </div>
                                </div>
                                @if($i < 3)
                                    <div class="h-px bg-gradient-to-r from-transparent via-white/5 to-transparent"></div>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-5 text-center">
                            <span class="text-[10px] text-gray-700 font-cinzel tracking-widest uppercase">
                                Updates every hour
                            </span>
                        </div>
                    </div>

                    {{-- Corner accent --}}
                    <div class="absolute -top-2 -right-2 w-16 h-16 pointer-events-none"
                         style="background: radial-gradient(circle at top right, rgba(245,197,66,0.15), transparent 70%);">
                    </div>
                </div>

            </div>
        </div>

    </section>

    {{-- ===================== FEATURED GAMES ===================== --}}
    <section id="games" class="py-10 bg-[#0a0a0a]">
        @php
            $featuredGames = [
                ['image' => asset('casino/kadi.png'),          'title' => 'Kadi',            'category' => 'Card Game'],
                ['image' => asset('casino/slots.png'),'title' => 'Golden Slots',    'category' => 'Slots'],
                ['image' => asset('casino/roulette.png'),    'title' => 'Roulette Noir',   'category' => 'Table Game'],
                ['image' => asset('casino/poker.png'),       'title' => 'Royal Poker',     'category' => 'Poker'],
                ['image' => asset('casino/king.png'),   'title' => 'Royal BlackJack', 'category' => 'Table Game'],
                ['image' => asset('casino/dice.png'),          'title' => 'Royal Dice',      'category' => 'Dice'],
                ['image' => asset('casino/slots.png'),'title' => 'Mega Slots',      'category' => 'Slots'],
                ['image' => asset('casino/crown.png'),       'title' => 'Texas Hold\'em',  'category' => 'Poker'],
            ];
        @endphp

        <x-carousel
            title="🎰 Featured Games"
            subtitle="Top picks from our casino floor"
            view-all-link="{{ route('login') }}"
        >
            @foreach($featuredGames as $game)
                <div class="group relative rounded-2xl overflow-hidden cursor-pointer
                            flex-shrink-0 w-56 sm:w-64 h-72 bg-[#111]
                            border border-[#222] hover:border-[#f5c542]/40 transition-all duration-300
                            snap-start shadow-lg hover:shadow-[0_8px_30px_rgba(245,197,66,0.15)]">

                    <img
                        src="{{ $game['image'] }}"
                        alt="{{ $game['title'] }}"
                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                        onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #1a1a1a 0%, #2a1f00 100%)';"
                    />

                    <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/90 to-transparent pt-12 pb-4 px-4">
                        @if(!empty($game['category']))
                            <span class="text-[10px] font-bold uppercase text-[#f5c542] bg-[#f5c542]/10 border border-[#f5c542]/20 px-2 py-0.5 rounded block w-fit mb-1">
                                {{ $game['category'] }}
                            </span>
                        @endif
                        <h3 class="text-white font-bold text-sm leading-tight mb-2">{{ $game['title'] }}</h3>
                        <a href="{{ route('login') }}" wire:navigate
                           class="inline-flex items-center gap-1 text-xs font-bold text-black bg-[#f5c542] hover:bg-[#ffde74] px-3 py-1.5 rounded transition-colors duration-150">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Play Now
                        </a>
                    </div>

                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500
                                bg-gradient-to-br from-white/5 via-transparent to-transparent pointer-events-none"></div>
                </div>
            @endforeach
        </x-carousel>
    </section>

    {{-- ===================== POPULAR GAMES ===================== --}}
    <section class="py-10 bg-[#0d0d0d]">
        @php
            $popularGames = [
                ['image' => asset('casino/kadi.png'),          'title' => 'Kadi',            'badge' => '🟢 Live Dealer',  'players' => rand(120, 850)],
                ['image' => asset('casino/slots.png'),'title' => 'Golden Slots',    'badge' => '📊 RTP 97.4%',    'players' => rand(200, 1200)],
                ['image' => asset('casino/roulette.png'),      'title' => 'Roulette Noir',   'badge' => '👑 VIP Room',     'players' => rand(80, 600)],
                ['image' => asset('casino/poker.png'),         'title' => 'Royal Poker',     'badge' => '🏆 Prize Pool',   'players' => rand(50, 400)],
                ['image' => asset('casino/crown.png'),   'title' => 'Royal BlackJack', 'badge' => '🃏 Classic',      'players' => rand(60, 500)],
                ['image' => asset('casino/king.png'),        'title' => 'Casino Royale',   'badge' => '✨ Featured',     'players' => rand(90, 700)],
            ];
        @endphp

        <x-carousel
            title="🔥 Popular Games"
            subtitle="Most played this week"
            view-all-link="{{ route('login') }}"
        >
            @foreach($popularGames as $pg)
                <div class="group relative rounded-xl overflow-hidden flex-shrink-0 w-48 sm:w-56 h-60 bg-[#111]
                            border border-[#222] hover:border-[#f5c542]/40 transition-all duration-300 snap-start">

                    <img
                        src="{{ $pg['image'] }}"
                        alt="{{ $pg['title'] }}"
                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                        onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #1a1a1a 0%, #2a1f00 100%)';"
                    />

                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/85 to-transparent pt-10 pb-3 px-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[9px] font-bold text-[#f5c542]">{{ $pg['badge'] }}</span>
                            <span class="text-[9px] text-gray-500">{{ $pg['players'] }} playing</span>
                        </div>
                        <h3 class="text-white font-semibold text-sm leading-tight mb-2">{{ $pg['title'] }}</h3>
                        <a href="{{ route('login') }}" wire:navigate
                           class="text-[#f5c542] text-xs font-bold hover:underline">
                            Play →
                        </a>
                    </div>
                </div>
            @endforeach
        </x-carousel>
    </section>

    {{-- ===================== SPORTS BETTING ===================== --}}
    <section class="py-10 bg-[#0a0a0a]">
        @php
            $sportsbookCards = [
                ['league' => 'Premier League', 'time' => 'Today 20:00',    'home' => 'Manchester City', 'away' => 'Arsenal',       'odds' => ['2.10', '3.40', '3.20']],
                ['league' => 'La Liga',        'time' => 'Tomorrow 21:00', 'home' => 'Barcelona',       'away' => 'Real Madrid',   'odds' => ['2.50', '3.10', '2.80']],
                ['league' => 'NBA',            'time' => 'Today 02:30',    'home' => 'LA Lakers',       'away' => 'Boston Celtics','odds' => ['1.85', '—',    '1.95']],
                ['league' => 'Serie A',        'time' => 'Sat 19:45',      'home' => 'AC Milan',        'away' => 'Juventus',      'odds' => ['2.30', '3.20', '2.90']],
                ['league' => 'Bundesliga',     'time' => 'Sat 17:30',      'home' => 'Bayern Munich',   'away' => 'Dortmund',      'odds' => ['1.75', '3.80', '4.50']],
            ];
        @endphp

        <x-carousel
            title="⚽ Sports Betting"
            subtitle="Live odds — updated every 2 hours"
            view-all-link="{{ route('sportsbook') }}"
            view-all-text="Full Sportsbook"
        >
            @foreach($sportsbookCards as $card)
                <div class="flex-shrink-0 w-64 snap-start bg-[#111] border border-[#222] rounded-xl p-4
                            hover:border-[#f5c542]/40 transition-all duration-200">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[10px] text-[#f5c542] font-bold uppercase tracking-wide bg-[#1a1200] px-2 py-0.5 rounded">
                            {{ $card['league'] }}
                        </span>
                        <span class="text-[10px] text-gray-500">{{ $card['time'] }}</span>
                    </div>
                    <div class="text-center mb-3">
                        <div class="text-white font-bold text-sm">{{ $card['home'] }}</div>
                        <div class="text-gray-600 text-xs my-1">vs</div>
                        <div class="text-white font-bold text-sm">{{ $card['away'] }}</div>
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 mb-3">
                        @foreach([['1', $card['odds'][0]], ['X', $card['odds'][1]], ['2', $card['odds'][2]]] as [$label, $price])
                            <div class="bg-[#1e1e2e] text-center py-2 rounded border border-[#2a2a3e] hover:border-[#f5c542] transition cursor-pointer">
                                <div class="text-[10px] text-gray-500">{{ $label }}</div>
                                <div class="text-[#f5c542] font-bold text-sm">{{ $price }}</div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('sportsbook') }}" wire:navigate
                       class="block w-full text-center bg-[#f5c542] text-black font-bold py-2 rounded hover:bg-[#ffde74] transition text-xs">
                        Bet Now
                    </a>
                </div>
            @endforeach

            {{-- "View All" end card --}}
            <div class="flex-shrink-0 w-48 snap-start flex flex-col items-center justify-center
                        bg-[#111] border border-dashed border-[#333] rounded-xl p-6 text-center">
                <div class="text-3xl mb-2">🏆</div>
                <div class="text-gray-400 text-sm font-semibold mb-3">More Sports</div>
                <a href="{{ route('sportsbook') }}" wire:navigate
                   class="text-xs font-bold text-black bg-[#f5c542] hover:bg-[#ffde74] px-4 py-2 rounded transition">
                    View All
                </a>
            </div>
        </x-carousel>
    </section>

    {{-- ===================== WHY CHOOSE US ===================== --}}
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

    {{-- ===================== PROMOTIONS BANNER ===================== --}}
    <section id="promotions" class="py-20" style="background: linear-gradient(135deg, #1a1000, #2a1f00, #1a1000);">
        <div class="mx-auto max-w-7xl px-6">
            <div class="glass-card flex flex-col items-start gap-6 border-l-4 border-[#f5c542] p-8 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="mb-3 inline-flex rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-3 py-1 text-xs tracking-widest text-[#f5c542]">
                        🎁 WELCOME OFFER
                    </div>
                    <h2 class="mb-2 text-3xl font-bold text-[#f5c542] md:text-4xl" style="font-family: 'Cinzel', serif;">
                        100% Match Bonus up to {{ session('currency.code', 'KES') }} 50,000
                    </h2>
                    <p class="text-[#f5f5f0]/60" style="font-family: 'Outfit', sans-serif;">Plus 50 free spins on your first deposit. T&Cs apply.</p>
                </div>
                <a href="{{ route('register') }}" wire:navigate
                   class="btn-casino-primary shrink-0 inline-block rounded-full px-8 py-4 no-underline">
                    Claim Bonus →
                </a>
            </div>
        </div>
    </section>

    {{-- ===================== STATS BAR ===================== --}}
    <section class="border-y border-[#f5c542]/30 bg-black py-12">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                @php
                    $stats = [
                        ['value' => '10,000+', 'label' => 'Players Active'],
                        ['value' => '$2M+',    'label' => 'Total Paid Out'],
                        ['value' => '50+',     'label' => 'Casino Games'],
                        ['value' => '24/7',    'label' => 'Live Support'],
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
