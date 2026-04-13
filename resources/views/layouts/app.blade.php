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
                }
            }"
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
                :class="{
                    'translate-x-0': sidebarOpen,
                    '-translate-x-full': !sidebarOpen,
                    'lg:w-56': expanded,
                    'lg:w-16': !expanded
                }"
                class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col border-r border-yellow-800/30 bg-[#111111] transition-all duration-300 ease-in-out lg:static lg:translate-x-0 lg:overflow-y-auto flex-shrink-0"
            >
                {{-- Logo --}}
                <div class="flex h-16 items-center border-b border-yellow-800/20 overflow-hidden flex-shrink-0"
                     :class="expanded ? 'px-6 justify-start' : 'px-0 justify-center'">
                    <a href="{{ route('home') }}"
                       class="text-lg tracking-widest text-[#f5c542] flex-shrink-0"
                       style="font-family: 'Cinzel', serif;"
                       wire:navigate>
                        <span x-show="expanded" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="whitespace-nowrap">♠ KADI KINGS</span>
                        <span x-show="!expanded">♠</span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex flex-1 flex-col gap-1 p-2 overflow-y-auto">

                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}" wire:navigate
                       :class="expanded ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Dashboard' : ''"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                           'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('dashboard'),
                           'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('dashboard'),
                       ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span x-show="expanded" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Dashboard</span>
                    </a>

                    {{-- Games --}}
                    <a href="{{ route('games') }}" wire:navigate
                       :class="expanded ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Games' : ''"
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

                    {{-- Sportsbook --}}
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

                    {{-- Profile --}}
                    <a href="{{ route('profile') }}" wire:navigate
                       :class="expanded ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Profile' : ''"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                           'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('profile'),
                           'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('profile'),
                       ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span x-show="expanded" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Profile</span>
                    </a>

                    {{-- Wallet --}}
                    <a href="{{ route('wallet') }}" wire:navigate
                       :class="expanded ? 'justify-start' : 'justify-center px-0'"
                       :title="!expanded ? 'Wallet' : ''"
                       @class([
                           'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all',
                           'bg-[#f5c542]/10 text-[#f5c542] border-l-2 border-[#f5c542]'                             => request()->routeIs('wallet'),
                           'text-gray-400 hover:text-white hover:bg-[#161616] border-l-2 border-transparent'        => !request()->routeIs('wallet'),
                       ])>
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span x-show="expanded" x-transition:enter="transition-opacity duration-200"
                              x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                              class="text-sm font-medium whitespace-nowrap">Wallet</span>
                    </a>

                    {{-- Logout --}}
                    <div class="mt-auto pt-4 border-t border-yellow-800/20">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    :class="expanded ? 'justify-start' : 'justify-center px-0'"
                                    :title="!expanded ? 'Logout' : ''"
                                    class="flex w-full items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 transition hover:bg-red-500/5 hover:text-red-400 border-l-2 border-transparent">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span x-show="expanded" x-transition:enter="transition-opacity duration-200"
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
                        {{-- Balance badge --}}
                        @php $navBalance = \Illuminate\Support\Facades\Cache::get('kadi.customer.'.auth()->id(), [])['balance'] ?? 0; @endphp
                        <div class="flex items-center gap-2 rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-4 py-1.5">
                            <span class="text-sm">💰</span>
                            <x-currency-amount :amount="$navBalance" class="text-sm font-semibold text-[#f5c542]" style="font-family: 'Cinzel', serif;" />
                        </div>
                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-casino-primary inline-block rounded-full px-5 py-2 text-sm no-underline">
                                <span>🚪</span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </header>

                {{-- Page content --}}
                <main class="flex-1 overflow-auto bg-[#0a0a0a] p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-structured-data :page="$page ?? 'dashboard'" :noindex="true" />
        @fluxScripts
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('betSlip', {
                selections: {},

                add(eventId, selectionKey, team, price, homeTeam, awayTeam, marketKey, marketLabel, commenceTime, isLive) {
                    // Always key by eventId — only one selection per event allowed
                    const existing = this.selections[eventId];

                    // Toggle: if clicking the EXACT same team + market, remove it
                    if (existing && existing.team === team && existing.marketKey === (marketKey || 'h2h')) {
                        delete this.selections[eventId];
                    } else {
                        // Replace any existing selection for this event
                        this.selections[eventId] = {
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
                    Livewire.dispatch('alpine-bet-slip-updated', {
                        selections: Object.fromEntries(Object.entries(this.selections))
                    });
                    window.dispatchEvent(new CustomEvent('bet-slip-updated'));
                },

                remove(eventId) {
                    delete this.selections[eventId];
                    Livewire.dispatch('alpine-bet-slip-updated', {
                        selections: Object.fromEntries(Object.entries(this.selections))
                    });
                },

                clear() {
                    this.selections = {};
                    Livewire.dispatch('alpine-bet-slip-updated', { selections: {} });
                },

                isSelected(eventId, team, marketKey) {
                    const sel = this.selections[eventId];
                    return sel && sel.team === team && sel.marketKey === (marketKey || 'h2h');
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
