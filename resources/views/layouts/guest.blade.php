<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#0a0a0a] antialiased" style="font-family: 'Outfit', sans-serif;">
        @include('partials.preloader')

        {{-- Navbar --}}
        <nav
            x-data="{ scrolled: false, menuOpen: false }"
            @bottom-nav:toggle-menu.window="menuOpen = true"
            x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 60 })"
            :class="scrolled || menuOpen ? 'bg-black/80 backdrop-blur-xl border-b border-[#f5c542]/10 shadow-lg' : 'bg-transparent border-b border-transparent'"
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 ease-in-out"
        >
            {{-- Top bar --}}
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="text-xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;" wire:navigate>
                    ♠ {{ strtoupper(config('app.name')) }}
                </a>

                {{-- Desktop nav links --}}
                <div class="hidden items-center gap-8 md:flex">
                    <a href="{{ route('home') }}" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]" wire:navigate>Home</a>
                    <a href="{{ route('rules') }}" wire:navigate class="text-sm transition {{ request()->routeIs('rules') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Rules</a>
                    {{--
                    <a href="{{ route('guest.games') }}" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]" wire:navigate>Casino</a>
                    <a href="{{ route('sportsbook') }}" wire:navigate class="text-sm transition {{ request()->routeIs('sportsbook') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Sports</a>
                    --}}
                    @auth
{{--                        <a href="{{ route('buy-coins') }}" wire:navigate class="text-sm transition {{ request()->routeIs('buy-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Buy Coins</a>--}}
{{--                        <a href="{{ route('earn-coins') }}" wire:navigate class="text-sm transition {{ request()->routeIs('earn-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Free Coins</a>--}}
                        <a href="{{ url('/marketing/ad-campaigns') }}" wire:navigate class="text-sm transition {{ request()->routeIs('earn-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Campaigns</a>
                        <a href="{{ route('profile') }}" wire:navigate class="text-sm transition {{ request()->routeIs('earn-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Profile</a>
                    @endauth
                    {{--
                     <a href="#about" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">About</a>
                    <a href="#promotions" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Promotion</a>
                     --}}
                </div>

                {{-- Right side: auth button + hamburger --}}
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-3">
                        @auth
                            <livewire:wallet-balance wire:key="wallet-balance-desktop" />
                            <livewire:notifications-bell wire:key="notifications-bell-desktop" />
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-1.5 text-sm font-semibold text-[#f5c542] transition hover:text-[#ffde74] cursor-pointer bg-transparent border-0 p-0">
                                    <span>Logout</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" wire:navigate
                               class="btn-casino-ghost inline-block rounded-full px-5 py-2 text-sm no-underline">
                                Login
                            </a>
                        @endauth
                    </div>

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
                    <a href="{{ route('rules') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm transition {{ request()->routeIs('rules') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Rules</a>
                    {{--
                    <a href="{{ route('guest.games') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Casino</a>
                    <a href="{{ route('sportsbook') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm transition {{ request()->routeIs('sportsbook') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Sports</a>
                    --}}

                    @auth
{{--                        <a href="{{ route('buy-coins') }}" @click="menuOpen = false" wire:navigate--}}
{{--                           class="py-3 text-sm transition {{ request()->routeIs('buy-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Buy Coins</a>--}}
{{--                        <a href="{{ route('earn-coins') }}" @click="menuOpen = false" wire:navigate--}}
{{--                           class="py-3 text-sm transition {{ request()->routeIs('earn-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Free Coins</a>--}}
                        <a href="{{ url('/marketing/ad-campaigns') }}" @click="menuOpen = false" wire:navigate
                           class="py-3 text-sm transition {{ request()->routeIs('earn-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Campaigns</a>
                        <a href="{{ route('profile') }}" @click="menuOpen = false" wire:navigate
                           class="py-3 text-sm transition {{ request()->routeIs('earn-coins') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Profile</a>
                    @endauth

                    {{-- Auth CTA — mobile only --}}
                    <div class="py-4">
                        @auth
                            <div class="flex flex-col gap-1">
                                <div class="py-1.5">
                                    <livewire:wallet-balance wire:key="wallet-balance-mobile" />
                                </div>
                                <div class="py-1.5" @click="menuOpen = false">
                                    <livewire:notifications-bell wire:key="notifications-bell-mobile" />
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        @click="menuOpen = false"
                                        class="flex items-center gap-2 py-3 text-sm font-semibold text-[#f5c542] transition hover:text-[#ffde74] cursor-pointer bg-transparent border-0 p-0 w-full">
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex flex-col gap-3">
                                <a href="{{ route('login') }}" @click="menuOpen = false" wire:navigate
                                   class="btn-casino-primary block w-full rounded-xl py-3 text-center text-sm font-semibold no-underline">
                                    Enter the Arena 🎰
                                </a>
                                <a href="{{ route('register') }}" @click="menuOpen = false" wire:navigate
                                   class="btn-casino-ghost block w-full rounded-xl py-3 text-center text-sm font-semibold no-underline">
                                    Create Account →
                                </a>
                            </div>
                        @endauth
                    </div>
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
                        <div class="mb-4 text-xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;">♠ {{ strtoupper(config('app.name')) }}</div>
                        <p class="text-sm leading-relaxed text-[#6b6b6b]">
                            The home of competitive Kadi in Kenya. Play against real opponents, sharpen your strategy, and experience the excitement of online Kadi esports
                        </p>
                    </div>

                    {{-- Quick Links --}}
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-widest text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ route('rules') }}" wire:navigate class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]" wire:navigate>Rules & Card Guide</a></li>
                            <li><a href="{{ route('legal.terms') }}" wire:navigate class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]">Terms & Conditions</a></li>
                            <li><a href="{{ route('legal.privacy') }}" wire:navigate class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]">Privacy Policy</a></li>
                            {{--
                            <li><a href="{{ route('register') }}" class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]" wire:navigate>Register</a></li>
                            <li><a href="{{ route('login') }}" class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]" wire:navigate>Login</a></li>
                            --}}
                        </ul>
                    </div>

                    {{-- Support --}}
                    <div>
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-widest text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Support</h4>
                        <ul class="space-y-2">
                            <li><span class="text-sm text-[#6b6b6b]">+254790417280</span></li>
                            <li><span class="text-sm text-[#6b6b6b]">info@kadikings.co.ke</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border-t border-[#f5c542]/20 bg-black/50 px-6 py-4 text-center">
                <p class="text-xs text-[#6b6b6b]">
                    &copy; {{ date('Y') }} {{ strtoupper(config('app.name')) }}. All rights reserved. &nbsp;|&nbsp; Play Responsibly &nbsp;|&nbsp; 18+ since 2024
                </p>
            </div>
        </footer>

        <x-structured-data :page="$page ?? 'home'" />
        {{--
        @auth
            <livewire:phone-required />
        @endauth
        --}}
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
        @fluxScripts
        <livewire:navigation.bottom-nav />

    </body>
</html>
