<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="h-screen overflow-hidden bg-[#0a0a0a] antialiased" style="font-family: 'Outfit', sans-serif;">

        <div
            class="flex h-screen overflow-hidden"
            x-data="{
                sidebarOpen: false,
                expanded: JSON.parse(localStorage.getItem('sidebar_expanded') ?? 'false'),
                toggle() {
                    this.expanded = !this.expanded;
                    localStorage.setItem('sidebar_expanded', JSON.stringify(this.expanded));
                },
                showLabels() {
                    return this.expanded || window.innerWidth < 1024;
                }
            }"
            @bottom-nav:toggle-menu.window='sidebarOpen = true'
        >

            {{-- Sidebar overlay (mobile) --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-20 bg-black/60 lg:hidden"
                x-cloak
            ></div>

            {{-- Sidebar --}}
            <aside
                x-cloak
                :class="{
                    'translate-x-0': sidebarOpen,
                    '-translate-x-full': !sidebarOpen,
                    'w-64': true,
                    'lg:w-56': expanded,
                    'lg:w-16': !expanded
                }"
                class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col border-r border-yellow-800/30 bg-[#111111] transition-all duration-300 ease-in-out lg:static lg:translate-x-0 lg:overflow-y-auto flex-shrink-0"
            >
                {{-- Logo --}}
                <div class="flex h-16 items-center border-b border-yellow-800/20 overflow-hidden flex-shrink-0"
                     :class="showLabels() ? 'px-6 justify-start' : 'px-0 justify-center'">
                    <a href="{{ route('home') }}"
                       class="text-lg tracking-widest text-[#f5c542] flex-shrink-0"
                       style="font-family: 'Cinzel', serif;"
                       wire:navigate>
                        <span
                            x-show="showLabels()"
                            x-transition:enter="transition-opacity duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            class="whitespace-nowrap">♠ {{ strtoupper(config('app.name')) }}</span>
                        <span x-show="!expanded">♠</span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex flex-1 flex-col gap-1 p-2 overflow-y-auto">

                    {{-- ================= MAIN ================= --}}
                    <div
                        x-show="showLabels()"
                        class="px-3 pt-2 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500"
                    >
                        Main
                    </div>
                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}" wire:navigate
                       :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Dashboard' : ''"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                           'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('dashboard'),
                           'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('dashboard'),
                       ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span
                            x-show="showLabels()"
                            x-transition:enter="transition-opacity duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            class="text-sm font-medium whitespace-nowrap">Dashboard</span>
                    </a>

                    {{-- Divider
                    <div class="my-4 border-t border-yellow-800/20"></div>
                    --}}
                    {{-- ================= GAMING =================
                    <div
                        x-show="showLabels()"
                        class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500"
                    >
                        Gaming
                    </div>
                    --}}
                    {{-- Games
                    <a href="{{ route('games') }}" wire:navigate
                       :class="expanded ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Casino' : ''"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                           'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('games'),
                           'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('games'),
                       ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <span x-show="expanded" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Games</span>
                    </a>
                    --}}
                    {{-- Sportsbook
                    <a href="{{ route('dashboard.sportsbook') }}" wire:navigate
                       :class="expanded ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Sportsbook' : ''"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                           'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('dashboard.sportsbook'),
                           'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('dashboard.sportsbook'),
                       ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                        <span x-show="expanded" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Sportsbook</span>
                    </a>
                    --}}

                    {{-- Divider --}}
                    <div class="my-4 border-t border-yellow-800/20"></div>
                    {{-- ================= WALLET ================= --}}
                    <div
                        x-show="showLabels()"
                        class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500"
                    >
                        Wallet
                    </div>
                    {{-- Vault --}}
                    <a href="{{ route('wallet') }}" wire:navigate
                       :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Vault' : ''"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                           'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('wallet'),
                           'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('wallet'),
                       ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6 9a6 6 0 1112 0v5a4 4 0 01-4 4H9a4 4 0 01-4-4V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10 6h4"/>
                            <circle cx="12" cy="12" r="2.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 10.5v3M10.5 12H13.5"/>
                        </svg>
                        <span x-show="showLabels()" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Vault</span>
                    </a>
                    {{-- Buy Coins
                    <a href="{{ route('buy-coins') }}" wire:navigate
                       :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Buy Coins' : ''"
                        @class([
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                            'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('buy-coins'),
                            'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('buy-coins'),
                        ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8M8 12h8"/>
                        </svg>
                        <span x-show="showLabels()" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Buy Coins</span>
                    </a>
                    --}}
                    {{-- Earn Coins
                    <a href="{{ route('earn-coins') }}" wire:navigate
                       :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Free Coins' : ''"
                        @class([
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                            'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('earn-coins'),
                            'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('earn-coins'),
                        ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M4 9h16M5 9v10a2 2 0 002 2h10a2 2 0 002-2V9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 9C7 9 6 8 6 6.8 6 5.6 7 5 8.2 5c2 0 3.8 4 3.8 4M15.5 9c1.5 0 2.5-1 2.5-2.2C18 5.6 17 5 15.8 5c-2 0-3.8 4-3.8 4"/>
                        </svg>
                        <span x-show="showLabels()" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Free Coins</span>
                    </a>
                    --}}

                    {{-- Divider --}}
                    <div class="my-4 border-t border-yellow-800/20"></div>
                    {{-- ================= ACCOUNT ================= --}}
                    <div
                        x-show="showLabels()"
                        class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500"
                    >
                        Account
                    </div>
                    {{-- Profile --}}
                    <a href="{{ route('profile') }}" wire:navigate
                       :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Profile' : ''"
                        @class([
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                            'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('profile'),
                            'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('profile'),
                        ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span
                            x-show="showLabels()"
                            x-transition:enter="transition-opacity duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="text-sm font-medium whitespace-nowrap">Profile</span>
                    </a>

                    {{-- Divider
                    <div class="my-4 border-t border-yellow-800/20"></div>
                    --}}
                    {{-- ================= Store =================
                    <div
                        x-show="showLabels()"
                        class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500"
                    >
                        STORE
                    </div>
                    @if(auth()->user()->roles->isNotEmpty())
                        @if(auth()->user()->isAdmin())
                            <a href="{{ url('/console') }}"
                               :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                               :title="!expanded ? 'Manage Promotions' : ''"
                                @class([
                                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                                    'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('ad-campaigns'),
                                    'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('ad-campaigns'),
                                ])>
                                <svg class="w-5 h-5 flex-shrink-0"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M6 8h12l1 12H5L6 8z"/>
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M9 8V6a3 3 0 016 0v2"/>
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M15.5 11.5l1 1m0-1l-1 1"/>
                                </svg>
                                <span
                                    x-show="showLabels()"
                                    x-transition:enter="transition-opacity duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="text-sm font-medium whitespace-nowrap">Manage Promotions</span>
                            </a>
                        @endif
                        <a href="{{ route('storefront.home') }}" wire:navigate
                           :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                           :title="!expanded ? 'Store' : ''"
                            @class([
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                                'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('storefront.home'),
                                'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('storefront.home'),
                            ])>
                            <svg class="w-5 h-5 flex-shrink-0"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <rect x="4" y="4" width="6" height="6" rx="1.5"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"/>
                                <rect x="14" y="4" width="6" height="6" rx="1.5"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"/>
                                <rect x="4" y="14" width="6" height="6" rx="1.5"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"/>
                                <rect x="14" y="14" width="6" height="6" rx="1.5"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"/>
                            </svg>
                            <span
                                x-show="showLabels()"
                                x-transition:enter="transition-opacity duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                class="text-sm font-medium whitespace-nowrap">Store</span>
                        </a>
                    @endif
                    --}}

                    {{-- Divider --}}
                    <div class="my-4 border-t border-yellow-800/20"></div>
                    {{-- ================= ADVERTS ================= --}}
                    <div
                        x-show="showLabels()"
                        class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500"
                    >
                        ADVERTS
                    </div>
                    @if(auth()->user()->roles->isNotEmpty())
                        {{-- Campaings --}}
                        <a href="{{ url('/marketing') }}"
                           :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                           :title="!expanded ? 'Manage Ad Campaigns' : ''"
                            @class([
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                                'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('ad-campaigns'),
                                'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('ad-campaigns'),
                            ])>
                            <svg class="w-5 h-5 flex-shrink-0"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M4 11v2a2 2 0 002 2h2l3 4h2l-1.5-4H15l5 3V6l-5 3H6a2 2 0 00-2 2z"/>
                            </svg>
                            <span
                                x-show="showLabels()"
                                x-transition:enter="transition-opacity duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                class="text-sm font-medium whitespace-nowrap">Manage Campaigns</span>
                        </a>
                        {{-- Ad Wallet --}}
                        <a href="{{ route('ad-wallet') }}" wire:navigate
                           :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                           :title="!expanded ? 'Ad Vault' : ''"
                            @class([
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                                'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('ad-wallet'),
                                'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('ad-wallet'),
                            ])>
                            <svg class="w-5 h-5 flex-shrink-0"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M4 7a2 2 0 012-2h10a2 2 0 012 2v1H6a2 2 0 000 4h12v5a2 2 0 01-2 2H6a2 2 0 01-2-2V7z"/>
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M18 9h2v4h-2"/>
                                <circle cx="10" cy="14" r="2"/>
                            </svg>
                            <span
                                x-show="showLabels()"
                                x-transition:enter="transition-opacity duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                class="text-sm font-medium whitespace-nowrap">Ad Vault</span>
                        </a>
                    @endif


                    {{-- Logout --}}
                    <div class="mt-auto pt-4 border-t border-yellow-800/20">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    :class="showLabels() ? 'justify-start' : 'justify-center px-0'"
                                    :title="!expanded ? 'Logout' : ''"
                                    class="flex w-full items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 transition hover:bg-red-500/5 hover:text-red-400 border-l-2 border-transparent">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span x-show="showLabels()" x-transition:enter="transition-opacity duration-200"
                                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                      class="text-sm font-medium whitespace-nowrap">Logout</span>
                            </button>
                        </form>
                    </div>

                </nav>
            </aside>

            {{-- Main content --}}
            <div class="flex flex-1 flex-col min-w-0 overflow-hidden">

                {{-- Top bar --}}
                <header class="flex h-16 flex-shrink-0 items-center justify-between border-b border-yellow-800/20 bg-[#111111] px-6">
                    {{-- Hamburger — mobile: toggles overlay, desktop: toggles expand/collapse --}}
                    <button @click="window.innerWidth >= 1024 ? toggle() : (sidebarOpen = !sidebarOpen)" class="text-[#f5f5f0]/60 hover:text-[#f5c542] transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="hidden text-sm text-[#f5f5f0]/60 lg:block">
                        Welcome back, <span class="text-[#f5f5f0]">{{ auth()->user()->name }}</span>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Balance widget --}}
                        <livewire:wallet-balance />
                        {{-- Notifications bell --}}
                        <livewire:notifications-bell wire:key="notifications-bell-app" />
                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-casino-primary inline-block rounded-full px-5 py-2 text-sm no-underline">
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </header>

                {{-- Page content --}}
                <main id="app-scroll-container" class="flex-1 overflow-auto bg-[#0a0a0a] p-6 pb-24 lg:pb-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-structured-data :page="$page ?? 'dashboard'" :noindex="true" />
        @auth
            <livewire:phone-required />
        @endauth
        @fluxScripts
        <livewire:navigation.bottom-nav />
    </body>
</html>
