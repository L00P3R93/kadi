<div class="space-y-8">

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="rounded-lg border border-green-700 bg-green-900/30 p-4 text-sm text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Row 1: Welcome + Balance ── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Welcome card --}}
        <div class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-8">
            <h2 class="mb-2 text-2xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">
                Welcome back, {{ auth()->user()->name }}! 🎉
            </h2>
            <p class="mb-6 text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">Ready to play? Your luck starts now.</p>
            <div class="flex flex-wrap gap-3">
                <a href="#" class="btn-casino-primary inline-block rounded-full px-5 py-2 text-sm no-underline">
                    🎮 Play Games
                </a>
                <a href="{{ route('wallet') }}" wire:navigate
                   class="btn-casino-ghost inline-block rounded-full px-5 py-2 text-sm no-underline">
                    💰 View Wallet
                </a>
            </div>
        </div>

        {{-- Balance card --}}
        <div class="rounded-xl border border-[#f5c542]/40 bg-[#1a1a1a] p-8 shadow-[0_0_30px_rgba(245,197,66,0.08)]">
            <div class="mb-1 text-xs font-semibold uppercase tracking-widest text-[#6b6b6b]">Your Balance</div>
            <x-currency-amount :amount="$kadiBalance" class="mb-1 block text-5xl font-black text-[#f5c542]" style="font-family: 'Cinzel', serif;" />
            <div class="mb-6">
                <span class="rounded-full border border-[#f5c542]/30 bg-[#f5c542]/10 px-3 py-1 text-xs text-[#f5c542]">
                    {{ session('currency.code', 'KES') }} · Active
                </span>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('wallet') }}" wire:navigate
                   class="btn-casino-primary inline-block rounded-full px-5 py-2 text-sm no-underline">
                    + Deposit
                </a>
                <a href="{{ route('wallet') }}" wire:navigate
                   class="btn-casino-ghost inline-block rounded-full px-5 py-2 text-sm no-underline">
                    - Withdraw
                </a>
            </div>
        </div>
    </div>

    {{-- ── Featured Bonus + Live Jackpot row ── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Featured Bonus (col-8) --}}
        <section class="flex flex-col">
            <div class="mb-4">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#f5c542]/10 border border-[#f5c542]/30 text-[#f5c542] text-xs tracking-[0.25em] uppercase" style="font-family: 'Outfit', sans-serif;">
                    🎁 FEATURED BONUS
                </span>
            </div>

            <div class="relative flex-1 overflow-hidden rounded-2xl border border-[#f5c542]/20 p-8 md:p-10"
                 style="background: linear-gradient(135deg, #1a1200 0%, #1f1800 40%, #120d00 100%);">
                <div class="absolute top-0 right-0 w-96 h-96 rounded-full pointer-events-none"
                     style="background: radial-gradient(ellipse, rgba(245,197,66,0.08) 0%, transparent 70%); transform: translate(30%, -30%);"></div>
                <div class="absolute bottom-4 right-8 text-6xl opacity-10 text-[#f5c542] select-none">🪙</div>
                <div class="absolute top-4 right-32 text-4xl opacity-10 text-[#f5c542] select-none">♠</div>

                <div class="relative z-10 max-w-2xl">
                    <h2 class="font-black text-3xl md:text-4xl lg:text-5xl text-white leading-tight mb-4" style="font-family: 'Cinzel', serif;">
                        100% Welcome Bonus
                        <span class="block mt-1 shimmer-text">
                            Up To {{ session('currency.code', 'KES') }} {{ number_format(50000) }}
                        </span>
                    </h2>
                    <p class="text-gray-400 text-base md:text-lg leading-relaxed mb-8 max-w-xl" style="font-family: 'Outfit', sans-serif;">
                        Deposit today and unlock premium tables, free spins, and high-stakes tournaments designed for serious players. Enjoy a sleek experience built around class, excitement, and instant action.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('wallet') }}" wire:navigate
                           class="btn-casino-primary inline-flex items-center gap-2 px-8 py-3.5 rounded-xl text-sm no-underline">
                            🎁 Claim Bonus
                        </a>
                        <a href="#"
                           class="btn-casino-ghost inline-flex items-center gap-2 px-8 py-3.5 rounded-xl text-sm no-underline">
                            Explore Games
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Live Jackpot (col-4) --}}
        <section class="flex flex-col" wire:poll.900000ms="pollJackpot">
            <div class="mb-4">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-900/20 border border-red-500/30 text-red-400 text-xs tracking-[0.25em] uppercase" style="font-family: 'Outfit', sans-serif;">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse inline-block"></span>
                    LIVE JACKPOT
                </span>
            </div>

            <div class="relative flex-1 overflow-hidden rounded-2xl border border-red-900/30 p-6"
                 style="background: linear-gradient(135deg, #130000 0%, #1a0505 50%, #0d0000 100%);">
                <div class="absolute inset-0 pointer-events-none"
                     style="background: radial-gradient(ellipse at 50% 50%, rgba(255,50,50,0.06) 0%, transparent 70%);"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 rounded-full pointer-events-none"
                     style="border: 1px solid rgba(255,100,50,0.1); animation: pulseRing 2s ease-out infinite;"></div>

                <div class="relative z-10 text-center flex flex-col gap-6">
                    <div>
                        <p class="text-gray-500 text-xl uppercase tracking-widest mb-3" style="font-family: 'Outfit', sans-serif;">Current Progressive Prize Pool</p>
                        <p class="text-gray-500 text-xl uppercase tracking-[0.2em] mb-1" style="font-family: 'Outfit', sans-serif;">{{ session('currency.code', 'KES') }}</p>
                        <div class="font-black leading-none mb-3" style="font-family: 'Cinzel', serif; font-size: clamp(2rem, 5vw, 3.5rem); background: linear-gradient(135deg, #ff6b35, #ff4444, #ff9944, #ff4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 0 28px rgba(255,80,50,0.6));">
                            {{ number_format($jackpotAmount) }}
                        </div>
                        <div x-data="{
                            h: 2, m: 18, s: 44,
                            init() {
                                setInterval(() => {
                                    this.s--;
                                    if (this.s < 0) { this.s = 59; this.m--; }
                                    if (this.m < 0) { this.m = 59; this.h--; }
                                    if (this.h < 0) { this.h = 2; this.m = 18; this.s = 44; }
                                }, 1000)
                            }
                        }">
                            <p class="text-gray-400 text-xl mt-10" style="font-family: 'Outfit', sans-serif;">
                                ⏱ Next draw in
                                <span class="text-[#f5c542] font-semibold"
                                      x-text="`${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m ${String(s).padStart(2,'0')}s`">
                                    02h 18m 44s
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-5">
                        @foreach([['Live Tables', $liveTables], ['Active Games', $activeGames], ['Online Now', number_format($onlineUsers)]] as $stat)
                            <div class="text-center p-3 rounded-xl" style="background: rgba(255,80,50,0.06); border: 1px solid rgba(255,80,50,0.15);">
                                <div class="text-lg text-white font-bold" style="font-family: 'Cinzel', serif;">{{ $stat[1] }}</div>
                                <div class="text-gray-500 text-xs mt-1 uppercase tracking-wider leading-tight" style="font-family: 'Outfit', sans-serif;">{{ $stat[0] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

    </div>

    {{-- ── Popular Games ── --}}
    <section>
        <div class="text-center mb-8">
            <span class="inline-block px-4 py-1 rounded-full border border-[#f5c542]/40 text-[#f5c542] text-xs tracking-[0.2em] uppercase mb-4" style="font-family: 'Outfit', sans-serif;">✦ Most Played ✦</span>
            <h2 class="text-3xl text-white" style="font-family: 'Cinzel', serif;">POPULAR <span class="shimmer-text">GAMES</span></h2>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $popularGames = [
                    ['img'=>'/casino/kadi.png',       'name'=>'Kadi',          'desc'=>'Classic Kenyan card game. Thrilling Singles. Conquer Tournaments.','badge1'=>'🟢 Live Dealer','badge2'=>rand(120,850).' Playing','btn'=>'Join Now'],
                    ['img'=>'/casino/slots.png',      'name'=>'Golden Slots',  'desc'=>'Luxury slot action with rich visuals, bonus rounds, and glittering jackpots.','badge1'=>'📊 RTP 97.4%','badge2'=>rand(200,1200).' Playing','btn'=>'Spin Now'],
                    ['img'=>'/casino/roulette.png',   'name'=>'Roulette Noir', 'desc'=>'Spin the golden wheel and chase high-value wins in elegant style.','badge1'=>'👑 VIP Room','badge2'=>rand(80,600).' Playing','btn'=>'Spin Now'],
                    ['img'=>'/casino/poker.png',      'name'=>'Royal Poker',   'desc'=>'Challenge top players in the crown room and build your tournament stack.','badge1'=>'🏆 Prize Pool','badge2'=>rand(50,400).' Playing','btn'=>'Enter Room'],
                ];
            @endphp
            @foreach ($popularGames as $pg)
                <div class="overflow-hidden flex flex-col rounded-2xl border transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_0_20px_rgba(245,197,66,0.2)]"
                     style="background:#1a1a1a;border-color:rgba(245,197,66,0.2);">
                    <div class="relative flex items-center justify-center p-5" style="background: radial-gradient(ellipse at center, rgba(245,197,66,0.12) 0%, transparent 70%);">
                        <img src="{{ $pg['img'] }}" class="w-20 h-20 object-contain" alt="{{ $pg['name'] }}" />
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <div class="mb-2 flex flex-wrap gap-1.5">
                            <span class="stat-badge">{{ $pg['badge1'] }}</span>
                            <span class="stat-badge">{{ $pg['badge2'] }}</span>
                        </div>
                        <h3 class="mb-1 font-bold text-[#f5c542]" style="font-family: 'Cinzel', serif;">{{ $pg['name'] }}</h3>
                        <p class="mb-3 flex-1 text-xs text-[#f5f5f0]/50 line-clamp-2" style="font-family: 'Outfit', sans-serif;">{{ $pg['desc'] }}</p>
                        <a
                           wire:click="$set('showComingSoonModal', true)"
                           class="btn-casino-primary block w-full rounded-xl py-2 text-center text-xs no-underline">
                            {{ $pg['btn'] }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── Games Grid ── --}}
    <section>
        <h3 class="mb-6 text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Quick Play</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @php
                $quickPlayGames = [
                    ['img'=>'/casino/kadi.png',          'name'=>'Kadi'],
                    ['img'=>'/casino/slots.png',         'name'=>'Golden Slots'],
                    ['img'=>'/casino/roulette.png',      'name'=>'Roulette'],
                    ['img'=>'/casino/poker.png',       'name'=>'Royal Poker'],
                    ['img'=>'/casino/dice.png',          'name'=>'Royal Dice'],
                ];
            @endphp
            @foreach ($quickPlayGames as $game)
                <a wire:click="$set('showComingSoonModal', true)" class="group flex flex-col items-center gap-3 rounded-xl border border-yellow-900/30 bg-[#1a1a1a] p-5 text-center transition hover:-translate-y-1 hover:border-[#f5c542]/50">
                    <img src="{{ $game['img'] }}" class="w-10 h-10 object-contain" alt="{{ $game['name'] }}" />
                    <span class="text-xs font-semibold text-[#f5c542]" style="font-family: 'Cinzel', serif;">{{ $game['name'] }}</span>
                    <span class="text-xs text-[#6b6b6b] group-hover:text-[#f5c542]">Play →</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ── Sports Betting Preview ── --}}
    <livewire:dashboard.sports-betting-preview />

    {{-- ── Recent Transactions ── --}}
    <div class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-8">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Recent Transactions</h3>
            <a href="{{ route('wallet') }}" wire:navigate class="text-sm text-[#f5c542] hover:text-[#ffde74]">View All →</a>
        </div>

        @if ($recentTransactions->isEmpty())
            <div class="py-12 text-center">
                <div class="mb-3 text-4xl">🪙</div>
                <p class="text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">No transactions yet. Make your first deposit!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-yellow-800/20 text-left text-xs uppercase tracking-wider text-[#6b6b6b]">
                            <th class="pb-3">Date</th>
                            <th class="pb-3">Type</th>
                            <th class="pb-3">Amount</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-yellow-800/10">
                        @foreach ($recentTransactions as $tx)
                            <tr class="text-[#f5f5f0]/80">
                                <td class="py-3">{{ $tx->created_at->format('M d, Y') }}</td>
                                <td class="py-3">
                                    @if ($tx->type === 'deposit')
                                        <span class="rounded-full bg-green-900/50 px-2.5 py-1 text-xs text-green-400 border border-green-700">Deposit</span>
                                    @else
                                        <span class="rounded-full bg-red-900/50 px-2.5 py-1 text-xs text-red-400 border border-red-700">Withdrawal</span>
                                    @endif
                                </td>
                                <td class="py-3 font-semibold {{ $tx->type === 'deposit' ? 'text-green-400' : 'text-red-400' }}">
                                    <x-currency-amount :amount="$tx->amount" />
                                </td>
                                <td class="py-3">
                                    @if ($tx->status === 'completed')
                                        <span class="rounded-full bg-green-900/50 px-2.5 py-1 text-xs text-green-400">Completed</span>
                                    @elseif ($tx->status === 'pending')
                                        <span class="rounded-full bg-yellow-900/50 px-2.5 py-1 text-xs text-yellow-400">Pending</span>
                                    @else
                                        <span class="rounded-full bg-red-900/50 px-2.5 py-1 text-xs text-red-400">Failed</span>
                                    @endif
                                </td>
                                <td class="py-3 font-mono text-xs text-[#6b6b6b]">{{ $tx->reference }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Coming Soon Modal --}}
    <flux:modal wire:model="showComingSoonModal" class="max-w-sm">
        <div class="p-6 space-y-5">
            <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">💰 {{ $selectedGame ?: 'Play Game' }}</h3>
            <p class="text-sm text-[#6b6b6b]">Coming Soon. Please contact support to add funds to your account.</p>
            <flux:button wire:click="$set('showComingSoonModal', false)" variant="ghost" class="w-full">Close</flux:button>
        </div>
    </flux:modal>

</div>
