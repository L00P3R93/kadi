<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="h-screen overflow-hidden bg-[#0a0a0a] antialiased" style="font-family: 'Outfit', sans-serif;">

        <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

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
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col border-r border-yellow-800/30 bg-[#111111] transition-transform duration-300 lg:static lg:translate-x-0 lg:overflow-y-auto"
            >
                {{-- Logo --}}
                <div class="flex h-16 items-center px-6 border-b border-yellow-800/20">
                    <a href="{{ route('home') }}" class="text-lg tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;" wire:navigate>
                        ♠ KADI KINGS
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex flex-1 flex-col gap-1 p-4">
                    @php
                        $navItems = [
                            ['icon' => '🏠', 'label' => 'Dashboard', 'route' => 'dashboard'],
                            ['icon' => '🎮', 'label' => 'Games',     'route' => null],
                            ['icon' => '👤', 'label' => 'Profile',   'route' => 'profile'],
                            ['icon' => '💰', 'label' => 'Wallet',    'route' => 'wallet'],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        @if ($item['route'])
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                @class([
                                    'flex items-center gap-3 rounded-lg px-4 py-3 text-sm transition-all',
                                    'border-l-2 border-[#f5c542] bg-[#f5c542]/10 text-[#f5c542]'                                                      => request()->routeIs($item['route']),
                                    'border-l-2 border-transparent text-[#f5f5f0]/60 hover:border-[#f5c542]/50 hover:bg-[#f5c542]/5 hover:text-[#f5c542]' => !request()->routeIs($item['route']),
                                ])
                            >
                                <span>{{ $item['icon'] }}</span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span class="flex cursor-not-allowed items-center gap-3 rounded-lg border-l-2 border-transparent px-4 py-3 text-sm text-[#f5f5f0]/30">
                                <span>{{ $item['icon'] }}</span>
                                <span>{{ $item['label'] }}</span>
                                <span class="ml-auto text-xs">Soon</span>
                            </span>
                        @endif
                    @endforeach

                    <div class="mt-auto pt-4 border-t border-yellow-800/20">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg border-l-2 border-transparent px-4 py-3 text-sm text-[#f5f5f0]/60 transition hover:border-red-500/50 hover:bg-red-500/5 hover:text-red-400">
                                <span>🚪</span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            {{-- Main content --}}
            <div class="flex flex-1 flex-col min-w-0">

                {{-- Top bar --}}
                <header class="flex h-16 items-center justify-between border-b border-yellow-800/20 bg-[#111111] px-6">
                    {{-- Hamburger (mobile) --}}
                    <button @click="sidebarOpen = !sidebarOpen" class="text-[#f5f5f0]/60 lg:hidden">
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

        @fluxScripts
    </body>
</html>
