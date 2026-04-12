<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#0a0a0a] antialiased" style="font-family: 'Outfit', sans-serif;">

        {{-- Navbar --}}
        <nav
            x-data="{ scrolled: false, menuOpen: false }"
            x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 60 })"
            :class="scrolled || menuOpen ? 'bg-black/80 backdrop-blur-xl border-b border-[#f5c542]/10 shadow-lg' : 'bg-transparent border-b border-transparent'"
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 ease-in-out"
        >
            {{-- Top bar --}}
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="text-xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;" wire:navigate>
                    ♠ ANGEL PALACE
                </a>

                {{-- Desktop nav links --}}
                <div class="hidden items-center gap-8 md:flex">
                    <a href="{{ route('home') }}" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]" wire:navigate>Home</a>
                    <a href="{{ route('guest.games') }}" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]" wire:navigate>Games</a>
                    <a href="{{ route('sportsbook') }}" wire:navigate class="text-sm transition {{ request()->routeIs('sportsbook') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Sportsbook</a>
                    <a href="#about" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">About</a>
                    <a href="#promotions" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Promotions</a>
                </div>

                {{-- Right side: auth button + hamburger --}}
                <div class="flex items-center gap-3">
                    <!--<div class="hidden md:block">
                        @auth
                            <a href="{{ route('dashboard') }}" wire:navigate
                               class="btn-casino-primary inline-block rounded-full px-5 py-2 text-sm no-underline">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate
                               class="btn-casino-ghost inline-block rounded-full px-5 py-2 text-sm no-underline">
                                Login
                            </a>
                        @endauth
                    </div>-->

                    {{-- Hamburger (mobile only) --}}
                    <button
                        @click="menuOpen = !menuOpen"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#f5c542]/20 text-[#f5f5f0]/70 transition hover:border-[#f5c542]/50 hover:text-[#f5c542] md:hidden"
                        aria-label="Toggle menu"
                    >
                        <svg x-show="!menuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="menuOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile dropdown menu --}}
            <div
                x-show="menuOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                @click.away="menuOpen = false"
                class="border-t border-[#f5c542]/10 bg-black/90 backdrop-blur-xl md:hidden"
                x-cloak
            >
                <div class="flex flex-col divide-y divide-[#f5c542]/10 px-6 py-2">
                    <a href="{{ route('home') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Home</a>
                    <a href="{{ route('guest.games') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Games</a>
                    <a href="{{ route('sportsbook') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm transition {{ request()->routeIs('sportsbook') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">🏆 Sportsbook</a>
                    <a href="#about" @click="menuOpen = false"
                       class="py-3 text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">About</a>
                    <a href="#promotions" @click="menuOpen = false"
                       class="py-3 text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Promotions</a>

                    {{-- Auth CTA — mobile only --}}
                    <!--<div class="py-4">
                        @auth
                            <a href="{{ route('dashboard') }}" @click="menuOpen = false" wire:navigate
                               class="btn-casino-primary block w-full rounded-xl py-3 text-center text-sm font-semibold no-underline">
                                🎮 Go to Dashboard
                            </a>
                        @else
                            <div class="flex flex-col gap-3">
                                <a href="{{ route('login') }}" @click="menuOpen = false" wire:navigate
                                   class="btn-casino-primary block w-full rounded-xl py-3 text-center text-sm font-semibold no-underline">
                                    Enter the Casino 🎰
                                </a>
                                <a href="{{ route('register') }}" @click="menuOpen = false" wire:navigate
                                   class="btn-casino-ghost block w-full rounded-xl py-3 text-center text-sm font-semibold no-underline">
                                    Create Account →
                                </a>
                            </div>
                        @endauth
                    </div>-->
                </div>
            </div>
        </nav>

        {{-- Page content --}}
        {{ $slot }}

        {{-- Footer --}}
        <footer class="border-t border-[#f5c542]/30 bg-[#0a0a0a]">
            <div class="mx-auto max-w-7xl px-6 py-16">
                <div class="grid grid-cols-1 gap-12 md:grid-cols-3">
                    {{-- Brand --}}
                    <div>
                        <div class="mb-4 text-xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;">♠ ANGEL PALACE</div>
                        <p class="text-sm leading-relaxed text-[#6b6b6b]">
                            The premier destination for luxury online casino gaming. Experience the thrill of high-stakes entertainment from the comfort of your home.
                        </p>
                    </div>

                    {{-- Quick Links --}}
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-widest text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="#games" class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]">Games</a></li>
                            <li><a href="#promotions" class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]">Promotions</a></li>
                            <li><a href="{{ route('register') }}" class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]" wire:navigate>Register</a></li>
                            <li><a href="{{ route('login') }}" class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]" wire:navigate>Login</a></li>
                        </ul>
                    </div>

                    {{-- Support --}}
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-widest text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Support</h4>
                        <ul class="space-y-2">
                            <li><span class="text-sm text-[#6b6b6b]">24/7 Live Chat</span></li>
                            <li><span class="text-sm text-[#6b6b6b]">info@angelpalace.com</span></li>
                            <li><span class="text-sm text-[#6b6b6b]">Terms & Conditions</span></li>
                            <li><span class="text-sm text-[#6b6b6b]">Privacy Policy</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border-t border-[#f5c542]/20 bg-black/50 px-6 py-4 text-center">
                <p class="text-xs text-[#6b6b6b]">
                    &copy; {{ date('Y') }} ANGEL PALACE. All rights reserved. &nbsp;|&nbsp; Play Responsibly 🎰 &nbsp;|&nbsp; 18+
                </p>
            </div>
        </footer>

        <x-structured-data :page="$page ?? 'home'" />
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
        @fluxScripts
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('betSlip', {
                selections: {},

                add(eventId, selectionKey, team, price, homeTeam, awayTeam, marketKey, marketLabel, commenceTime, isLive) {
                    const key = selectionKey || eventId;
                    if (this.selections[key] && this.selections[key].team === team) {
                        delete this.selections[key];
                    } else {
                        this.selections[key] = {
                            team,
                            price:        parseFloat(price),
                            homeTeam,
                            awayTeam,
                            marketKey:    marketKey    || 'h2h',
                            marketLabel:  marketLabel  || 'Match Winner',
                            commenceTime: commenceTime || null,
                            isLive:       isLive       || false,
                            eventId,
                        };
                    }
                    Livewire.dispatch('alpine-guest-bet-slip-updated', {
                        selections: Object.fromEntries(Object.entries(this.selections))
                    });
                },

                remove(key) {
                    delete this.selections[key];
                    Livewire.dispatch('alpine-guest-bet-slip-updated', {
                        selections: Object.fromEntries(Object.entries(this.selections))
                    });
                },

                clear() {
                    this.selections = {};
                    Livewire.dispatch('alpine-guest-bet-slip-updated', { selections: {} });
                },

                isSelected(selectionKey, team) {
                    return this.selections[selectionKey] && this.selections[selectionKey].team === team;
                },

                count() { return Object.keys(this.selections).length; },

                potentialPayout(stake) {
                    if (!stake || isNaN(stake) || parseFloat(stake) <= 0) return '0.00';
                    let product = Object.values(this.selections).reduce((acc, s) => acc * s.price, 1);
                    return (parseFloat(stake) * product).toFixed(2);
                },

                formatTime(iso) {
                    if (!iso) return '';
                    // Convert UTC to EAT (UTC+3)
                    const d = new Date(new Date(iso).getTime() + (3 * 60 * 60 * 1000));
                    const days   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    const day   = days[d.getUTCDay()];
                    const date  = String(d.getUTCDate()).padStart(2,'0');
                    const month = months[d.getUTCMonth()];
                    const hh    = String(d.getUTCHours()).padStart(2,'0');
                    const mm    = String(d.getUTCMinutes()).padStart(2,'0');
                    return `${day} ${date} ${month}, ${hh}:${mm}`;
                }
            });
        });
        </script>
    </body>
</html>
