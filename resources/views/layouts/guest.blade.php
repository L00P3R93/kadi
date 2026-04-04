<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#0a0a0a] antialiased" style="font-family: 'Outfit', sans-serif;">

        {{-- Navbar --}}
        <nav
            x-data="{ scrolled: false }"
            x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 60 })"
            :class="scrolled ? 'bg-black/60 backdrop-blur-xl border-b border-[#f5c542]/10 shadow-lg' : 'bg-transparent border-b border-transparent'"
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 ease-in-out px-6 py-4"
        >
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="text-xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;" wire:navigate>
                    ♠ KADI KINGS
                </a>

                {{-- Nav links --}}
                <div class="hidden items-center gap-8 md:flex">
                    <a href="{{ route('home') }}" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]" wire:navigate>Home</a>
                    <a href="#games" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Games</a>
                    <a href="#about" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">About</a>
                    <a href="#promotions" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Promotions</a>
                </div>

                {{-- Auth buttons --}}
                <div class="flex items-center gap-3">
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
                        <!--<a href="{{ route('register') }}" wire:navigate
                           class="btn-casino-primary inline-block rounded-full px-5 py-2 text-sm no-underline">
                            Sign Up
                        </a> -->
                    @endauth
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
                        <div class="mb-4 text-xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;">♠ KADI KINGS</div>
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
                            <li><span class="text-sm text-[#6b6b6b]">support@kadikings.com</span></li>
                            <li><span class="text-sm text-[#6b6b6b]">Terms & Conditions</span></li>
                            <li><span class="text-sm text-[#6b6b6b]">Privacy Policy</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border-t border-[#f5c542]/20 bg-black/50 px-6 py-4 text-center">
                <p class="text-xs text-[#6b6b6b]">
                    &copy; {{ date('Y') }} KADI KINGS. All rights reserved. &nbsp;|&nbsp; Play Responsibly 🎰 &nbsp;|&nbsp; 18+
                </p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
