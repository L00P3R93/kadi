<div>
    {{-- ===================== HERO ===================== --}}
    <section class="hero-mesh relative flex min-h-screen items-center justify-center overflow-hidden">

        {{-- PNG floating symbols --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <img src="/casino/kadi.png"           class="casino-floater" style="top:10%;left:5%;width:80px;animation-delay:0s;animation-duration:7s;" />
            <img src="/casino/roulette.png"        class="casino-floater" style="top:15%;right:7%;width:95px;animation-delay:1.2s;animation-duration:9s;" />
            <img src="/casino/slot-machine-2.png"  class="casino-floater" style="top:55%;left:3%;width:70px;animation-delay:2.5s;animation-duration:8s;" />
            <img src="/casino/poker-1.png"         class="casino-floater" style="top:70%;right:5%;width:85px;animation-delay:0.7s;animation-duration:6.5s;" />
            <img src="/casino/blackjack-2.png"     class="casino-floater" style="top:35%;left:8%;width:65px;animation-delay:3.1s;animation-duration:10s;" />
            <img src="/casino/dice.png"            class="casino-floater" style="top:80%;left:15%;width:75px;animation-delay:1.8s;animation-duration:7.5s;" />
            <img src="/casino/kadi.png"            class="casino-floater" style="top:20%;right:20%;width:60px;animation-delay:4.0s;animation-duration:8.5s;" />
            <img src="/casino/roulette.png"        class="casino-floater" style="top:65%;right:15%;width:100px;animation-delay:2.2s;animation-duration:11s;" />
            <img src="/casino/slot-machine-2.png"  class="casino-floater" style="top:5%;right:40%;width:55px;animation-delay:0.5s;animation-duration:9.5s;" />
            <img src="/casino/poker-1.png"         class="casino-floater" style="top:45%;right:3%;width:72px;animation-delay:3.5s;animation-duration:7s;" />
        </div>

        {{-- Hero content --}}
        <div class="relative z-10 mx-auto max-w-4xl px-6 pt-24 text-center">
            {{-- Badge --}}
            <div class="mb-8 inline-flex items-center rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-6 py-2">
                <span class="text-xs font-semibold tracking-widest text-[#f5c542]">✦ PREMIUM ONLINE CASINO ✦</span>
            </div>

            {{-- Headline --}}
            <h1 class="shimmer-text mb-6 text-5xl font-black leading-tight md:text-7xl lg:text-8xl"
                style="font-family: 'Cinzel', serif;">
                WHERE FORTUNE<br>FAVORS THE BOLD
            </h1>

            {{-- Subheadline --}}
            <p class="mx-auto mb-8 max-w-xl text-xl text-[#f5f5f0]/60" style="font-family: 'Outfit', sans-serif;">
                Play the world's finest casino games. Win real prizes.
            </p>

            {{-- Gold divider --}}
            <div class="mb-10 flex items-center justify-center gap-4 text-[#f5c542]/60">
                <div class="h-px w-16 bg-[#f5c542]/30"></div>
                <span class="tracking-widest">♠ ♥ ♦ ♣</span>
                <div class="h-px w-16 bg-[#f5c542]/30"></div>
            </div>

            {{-- CTA buttons --}}
            <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" wire:navigate
                   class="btn-casino-primary inline-flex items-center gap-2 rounded-full px-8 py-4 text-lg no-underline transition-all hover:-translate-y-1">
                    🎰 Create Free Account
                </a>
                <a href="#games"
                   class="btn-casino-ghost inline-flex items-center gap-2 rounded-full px-8 py-4 text-lg no-underline">
                    Browse Games
                </a>
            </div>

            {{-- Scroll indicator --}}
            <div class="mt-20 flex justify-center">
                <div class="animate-bounce text-2xl text-[#f5c542]/60">↓</div>
            </div>
        </div>
    </section>

    {{-- ===================== FEATURED GAMES ===================== --}}
    <section id="games" class="py-24" style="background: linear-gradient(160deg, #0d0800 0%, #1a1200 50%, #0a0800 100%);">
        <div class="mx-auto max-w-7xl px-6">
            {{-- Section header --}}
            <div class="mb-16 text-center">
                <div class="mb-4 flex items-center justify-center gap-4">
                    <div class="h-px w-20 bg-[#f5c542]/30"></div>
                    <span class="text-sm tracking-widest text-[#f5c542]/60">♠</span>
                    <div class="h-px w-20 bg-[#f5c542]/30"></div>
                </div>
                <h2 class="text-4xl font-bold text-[#f5f5f0] md:text-5xl" style="font-family: 'Cinzel', serif;">
                    PLAY OUR GAMES
                </h2>
                <div class="mt-4 flex items-center justify-center gap-4">
                    <div class="h-px w-20 bg-[#f5c542]/30"></div>
                    <span class="text-sm tracking-widest text-[#f5c542]/60">♣</span>
                    <div class="h-px w-20 bg-[#f5c542]/30"></div>
                </div>
            </div>

            {{-- Game cards --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $games = [
                        ['img' => '/casino/kadi.png',          'name' => 'Kadi',            'tagline' => 'Classic Kenyan card game. Win big.'],
                        ['img' => '/casino/slot-machine-2.png','name' => 'Golden Slots',    'tagline' => 'Spin luxury reels. Chase glittering jackpots.'],
                        ['img' => '/casino/roulette.png',      'name' => 'Roulette Noir',   'tagline' => 'Golden wheel. High-value wins. Elegant style.'],
                        ['img' => '/casino/poker-1.png',       'name' => 'Royal Poker',     'tagline' => 'Challenge the crown room. Build your stack.'],
                        ['img' => '/casino/blackjack-2.png',   'name' => 'Royal BlackJack', 'tagline' => 'Beat the dealer. Rule the table.'],
                        ['img' => '/casino/dice.png',          'name' => 'Royal Dice',      'tagline' => 'Roll your way to riches.'],
                    ];
                @endphp

                @foreach ($games as $game)
                    <div class="game-card glass-card glass-card-hover group p-8">
                        <img src="{{ $game['img'] }}" class="w-16 h-16 object-contain mx-auto mb-4" alt="{{ $game['name'] }}" />
                        <h3 class="mb-2 text-center text-xl font-semibold text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                            {{ $game['name'] }}
                        </h3>
                        <p class="mb-6 text-center text-sm text-[#f5f5f0]/50" style="font-family: 'Outfit', sans-serif;">{{ $game['tagline'] }}</p>
                        <div class="text-center">
                            <a href="{{ route('register') }}" wire:navigate class="text-sm font-semibold text-[#f5c542] transition hover:text-[#ffde74]">
                                Play Now →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== POPULAR GAMES ===================== --}}
    <section class="py-24" style="background: linear-gradient(160deg, #0a0a0a 0%, #100d00 50%, #0a0a0a 100%);">
        <div class="mx-auto max-w-7xl px-6">
            <div class="text-center mb-12">
                <span class="inline-block px-4 py-1 rounded-full border border-[#f5c542]/40 text-[#f5c542] text-xs tracking-[0.2em] uppercase mb-4" style="font-family: 'Outfit', sans-serif;">
                    ✦ Most Played ✦
                </span>
                <h2 class="text-4xl text-white" style="font-family: 'Cinzel', serif;">POPULAR <span class="shimmer-text">GAMES</span></h2>
                <div class="flex items-center justify-center gap-3 mt-3">
                    <div class="h-px w-16 bg-gradient-to-r from-transparent to-[#f5c542]"></div>
                    <span class="text-[#f5c542] text-sm">♠</span>
                    <div class="h-px w-16 bg-gradient-to-l from-transparent to-[#f5c542]"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $popularGames = [
                        [
                            'img'    => '/casino/kadi.png',
                            'name'   => 'Kadi',
                            'desc'   => 'Classic Kenyan card game. Thrilling Singles. Conquer Tournaments. Rise Through the Jackpots.',
                            'badge1' => '🟢 Live Dealer',
                            'badge2' => rand(120, 850).' Playing',
                            'btn'    => 'Join Now',
                            'href'   => '/login',
                        ],
                        [
                            'img'    => '/casino/slot-machine-2.png',
                            'name'   => 'Golden Slots',
                            'desc'   => 'Luxury slot action with rich visuals, bonus rounds, and glittering jackpots.',
                            'badge1' => '📊 RTP 97.4%',
                            'badge2' => rand(200, 1200).' Playing',
                            'btn'    => 'Spin Now',
                            'href'   => '/login',
                        ],
                        [
                            'img'    => '/casino/roulette.png',
                            'name'   => 'Roulette Noir',
                            'desc'   => 'Spin the golden wheel and chase high-value wins in elegant style.',
                            'badge1' => '👑 VIP Room',
                            'badge2' => rand(80, 600).' Playing',
                            'btn'    => 'Spin Now',
                            'href'   => '/login',
                        ],
                        [
                            'img'    => '/casino/poker-1.png',
                            'name'   => 'Royal Poker',
                            'desc'   => 'Challenge top players in the crown room and build your tournament stack.',
                            'badge1' => '🏆 Prize Pool',
                            'badge2' => rand(50, 400).' Playing',
                            'btn'    => 'Enter Room',
                            'href'   => '/login',
                        ],
                    ];
                @endphp

                @foreach ($popularGames as $pg)
                    <div class="glass-card glass-card-hover overflow-hidden flex flex-col">
                        {{-- Image top --}}
                        <div class="relative flex items-center justify-center p-6" style="background: radial-gradient(ellipse at center, rgba(245,197,66,0.12) 0%, transparent 70%);">
                            <img src="{{ $pg['img'] }}" class="w-24 h-24 object-contain" alt="{{ $pg['name'] }}" />
                        </div>
                        {{-- Content bottom --}}
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3 flex flex-wrap gap-2">
                                <span class="stat-badge">{{ $pg['badge1'] }}</span>
                                <span class="stat-badge">{{ $pg['badge2'] }}</span>
                            </div>
                            <h3 class="mb-2 text-lg font-bold text-[#f5c542]" style="font-family: 'Cinzel', serif;">{{ $pg['name'] }}</h3>
                            <p class="mb-4 flex-1 text-sm text-[#f5f5f0]/50 line-clamp-3" style="font-family: 'Outfit', sans-serif;">{{ $pg['desc'] }}</p>
                            <a href="{{ $pg['href'] }}" wire:navigate
                               class="btn-casino-primary block w-full rounded-xl py-2.5 text-center text-sm no-underline">
                                {{ $pg['btn'] }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== SPORTS BETTING — COMING SOON ===================== --}}
    <section class="sports-section py-20">
        <div class="scan-sweep"></div>
        <div class="mx-auto max-w-7xl px-6 relative z-10">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 items-center">

                {{-- Left column --}}
                <div>
                    <span class="inline-flex items-center rounded-full border border-green-500/40 px-4 py-1 text-xs uppercase tracking-widest text-green-400 mb-6">
                        COMING SOON
                    </span>
                    <h2 class="mb-2 text-4xl font-black text-white md:text-5xl" style="font-family: 'Cinzel', serif;">SPORTS BETTING</h2>

                    {{-- Cycling sub-headline --}}
                    <div x-data="{
                        phrases: ['Premier League · La Liga · Serie A', 'NBA · NFL · Champions League', 'Cricket · Rugby · Formula 1', 'KPL · NSL · Harambee Stars'],
                        current: 0,
                        init() { setInterval(() => { this.current = (this.current + 1) % this.phrases.length }, 2500) }
                    }" class="mb-4 h-7">
                        <p x-text="phrases[current]" class="text-green-400 text-lg transition-all duration-500" style="font-family: 'Outfit', sans-serif;"></p>
                    </div>

                    <p class="mb-6 text-[#6b6b6b] leading-relaxed" style="font-family: 'Outfit', sans-serif;">
                        The stadium is almost ready. KADI KINGS is launching the most electrifying sportsbook in East Africa — live odds, instant cashout, and premium markets across 40+ global sports.
                    </p>

                    {{-- Notify me form --}}
                    <div class="flex gap-2 mb-6">
                        <input type="email" placeholder="Enter your email for early access"
                            class="flex-1 bg-white/5 border border-green-800/40 rounded-lg px-4 py-3 text-white text-sm placeholder-gray-600 focus:outline-none focus:border-green-500/60" style="font-family: 'Outfit', sans-serif;" />
                        <button class="btn-casino-sports px-6 py-3 rounded-lg text-sm font-semibold uppercase tracking-wider" style="font-family: 'Cinzel', serif;">
                            Notify Me
                        </button>
                    </div>

                    {{-- Countdown --}}
                    <div x-data="{
                        target: new Date(Date.now() + 90*24*60*60*1000),
                        days: 0, hours: 0, mins: 0, secs: 0,
                        init() {
                            setInterval(() => {
                                const diff = this.target - new Date();
                                this.days  = Math.floor(diff / 86400000);
                                this.hours = Math.floor((diff % 86400000) / 3600000);
                                this.mins  = Math.floor((diff % 3600000) / 60000);
                                this.secs  = Math.floor((diff % 60000) / 1000);
                            }, 1000)
                        }
                    }">
                        <div class="flex gap-4 mt-4">
                            <div class="countdown-box" x-text="String(days).padStart(2,'0')">00</div>
                            <span class="text-green-400 text-2xl self-center">:</span>
                            <div class="countdown-box" x-text="String(hours).padStart(2,'0')">00</div>
                            <span class="text-green-400 text-2xl self-center">:</span>
                            <div class="countdown-box" x-text="String(mins).padStart(2,'0')">00</div>
                            <span class="text-green-400 text-2xl self-center">:</span>
                            <div class="countdown-box" x-text="String(secs).padStart(2,'0')">00</div>
                        </div>
                        <p class="text-xs text-gray-600 mt-2 tracking-widest uppercase" style="font-family: 'Outfit', sans-serif;">Days · Hours · Minutes · Seconds</p>
                    </div>
                </div>

                {{-- Right column --}}
                <div>
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        @foreach([['⚽','Football'],['🏀','Basketball'],['🎾','Tennis'],['🏏','Cricket'],['🏉','Rugby'],['🏎️','Formula 1']] as $i => $sport)
                            <div class="sport-pill" style="animation-delay: {{ $i * 0.15 }}s">
                                <span class="text-2xl">{{ $sport[0] }}</span>
                                <span class="text-xs text-gray-400 mt-1" style="font-family: 'Outfit', sans-serif;">{{ $sport[1] }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Sports ticker --}}
                    <div class="overflow-hidden border-t border-b border-green-900/30 py-2">
                        <div class="flex gap-8 animate-marquee whitespace-nowrap">
                            @foreach(['MAN UTD vs ARSENAL','CHELSEA vs LIVERPOOL','REAL MADRID vs BARCA','INTER vs JUVENTUS','PSG vs LYON','REDS vs BULLS','WARRIORS vs LAKERS'] as $match)
                                <span class="text-green-400/60 text-xs tracking-wider" style="font-family: 'Outfit', sans-serif;">⚡ {{ $match }}</span>
                            @endforeach
                            @foreach(['MAN UTD vs ARSENAL','CHELSEA vs LIVERPOOL','REAL MADRID vs BARCA','INTER vs JUVENTUS','PSG vs LYON','REDS vs BULLS','WARRIORS vs LAKERS'] as $match)
                                <span class="text-green-400/60 text-xs tracking-wider" style="font-family: 'Outfit', sans-serif;">⚡ {{ $match }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                    Claim Your Bonus →
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
