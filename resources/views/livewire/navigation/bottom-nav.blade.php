{{--
    Sticky, floating bottom nav — visible on tablet and below (lg:hidden),
    matching the breakpoint your sidebar already collapses at in
    app.blade.php. Lives in the persistent layout shell (outside {{ $slot }})
    so wire:navigate page swaps never tear this down or reset scroll state.

    Requires:
      - Alpine.data('bottomNav', ...) registered — see bottom-nav-alpine.js
      - #app-scroll-container id on the scrollable <main> in app.blade.php
      - CSS from bottom-nav.css merged into app.css
--}}
<nav
    id="app-bottom-nav"
    x-data="bottomNav()"
    :class="hidden ? 'translate-y-[130%]' : 'translate-y-0'"
    class="fixed inset-x-0 bottom-0 z-40 lg:hidden px-3 transition-transform duration-300 ease-in-out"
    style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));"
    aria-label="Primary"
>
    <div
        class="glass-card mx-auto flex max-w-md items-stretch justify-between gap-0.5 rounded-2xl px-1 py-1"
        style="background: rgba(17, 17, 17, 0.92); border-color: rgba(245, 197, 66, 0.22); box-shadow: 0 8px 32px rgba(0,0,0,0.55), 0 0 24px rgba(245,197,66,0.12);"
    >
        @foreach ($this->items as $item)
            @if (($item['type'] ?? 'link') === 'action')
                {{-- "Menu" action: dispatch a window event, don't navigate.
                     Wire this up in app.blade.php's root x-data, e.g.:
                     @bottom-nav:toggle-menu.window="sidebarOpen = true" --}}
                <button
                    type="button"
                    @click="$dispatch('bottom-nav:toggle-menu')"
                    class="bottom-nav-item"
                    aria-label="{{ $item['label'] }}"
                >
                    <svg class="bottom-nav-item__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="bottom-nav-item__label">{{ $item['label'] }}</span>
                </button>
            @else
                @php($active = $this->isActive($item))
                <a
                    href="{{ $item['url'] ? url($item['url']) : route($item['route']) }}"
                    wire:navigate
                    @class(['bottom-nav-item', 'bottom-nav-item--active' => $active])
                    aria-label="{{ $item['label'] }}"
                    @if ($active) aria-current="page" @endif
                >
                    <span class="bottom-nav-item__icon-wrap">
                        @switch($item['key'])
                            @case('home')
                                <svg class="bottom-nav-item__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                @break
                            @case('store')
                                <svg class="bottom-nav-item__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="4" y="4" width="6" height="6" rx="1.5" />
                                    <rect x="14" y="4" width="6" height="6" rx="1.5" />
                                    <rect x="4" y="14" width="6" height="6" rx="1.5" />
                                    <rect x="14" y="14" width="6" height="6" rx="1.5" />
                                </svg>
                                @break
                            @case('earn')
                                <svg class="bottom-nav-item__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M4 9h16M5 9v10a2 2 0 002 2h10a2 2 0 002-2V9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 9C7 9 6 8 6 6.8 6 5.6 7 5 8.2 5c2 0 3.8 4 3.8 4M15.5 9c1.5 0 2.5-1 2.5-2.2C18 5.6 17 5 15.8 5c-2 0-3.8 4-3.8 4" />
                                </svg>
                                @break
                            @case('wallet')
                                <svg class="bottom-nav-item__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 9a6 6 0 1112 0v5a4 4 0 01-4 4H9a4 4 0 01-4-4V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10 6h4"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 10.5v3M10.5 12H13.5"/>
                                </svg>
                                @break
                            @case('campaigns')
                                <svg class="bottom-nav-item__icon"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M4 11v2a2 2 0 002 2h2l3 4h2l-1.5-4H15l5 3V6l-5 3H6a2 2 0 00-2 2z"/>
                            </svg>
                                @break
                            @case('profile')
                                <svg class="bottom-nav-item__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                @break
                        @endswitch
                        @if ($active)
                            <span class="bottom-nav-item__dot" aria-hidden="true"></span>
                        @endif
                    </span>
                    <span class="bottom-nav-item__label">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</nav>
