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
                    <a href="{{ route('guest.games') }}" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]" wire:navigate>Casino</a>
                    <a href="{{ route('sportsbook') }}" wire:navigate class="text-sm transition {{ request()->routeIs('sportsbook') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Sports</a>
                    <a href="#about" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">About</a>
                    <a href="#promotions" class="text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Promotions</a>
                </div>

                {{-- Right side: auth button + hamburger --}}
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-3">
                        @auth
                            @php $navBalance = \Illuminate\Support\Facades\Cache::get('kadi.customer.'.auth()->id(), [])['balance'] ?? 0; @endphp
                            <a href="{{ route('wallet') }}" wire:navigate
                               class="flex items-center gap-2 rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-4 py-1.5 transition hover:border-[#f5c542]/70 hover:bg-[#f5c542]/20">
                                <span class="text-sm">💰</span>
                                <x-currency-amount :amount="$navBalance" class="text-sm font-semibold text-[#f5c542]" style="font-family: 'Cinzel', serif;" />
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn-casino-ghost inline-flex items-center gap-1.5 rounded-full px-5 py-2 text-sm">
                                    <span>🚪</span>
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
                    <a href="{{ route('guest.games') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm text-[#f5f5f0]/70 transition hover:text-[#f5c542]">Casino</a>
                    <a href="{{ route('sportsbook') }}" @click="menuOpen = false" wire:navigate
                       class="py-3 text-sm transition {{ request()->routeIs('sportsbook') ? 'text-[#f5c542] font-bold' : 'text-[#f5f5f0]/70 hover:text-[#f5c542]' }}">Sports</a>

                    {{-- Auth CTA — mobile only --}}
                    <div class="py-4">
                        @auth
                            @php $navBalance = \Illuminate\Support\Facades\Cache::get('kadi.customer.'.auth()->id(), [])['balance'] ?? 0; @endphp
                            <div class="flex flex-col gap-3">
                                <a href="{{ route('wallet') }}" @click="menuOpen = false" wire:navigate
                                   class="flex items-center justify-center gap-2 rounded-xl border border-[#f5c542]/40 bg-[#f5c542]/10 py-3 text-sm font-semibold text-[#f5c542]">
                                    <span>💰</span>
                                    <x-currency-amount :amount="$navBalance" class="font-bold" style="font-family: 'Cinzel', serif;" />
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" @click="menuOpen = false"
                                            class="btn-casino-ghost block w-full rounded-xl py-3 text-center text-sm font-semibold">
                                        🚪 Logout
                                    </button>
                                </form>
                            </div>
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
                    &copy; {{ date('Y') }} ANGEL PALACE. All rights reserved. &nbsp;|&nbsp; Play Responsibly &nbsp;|&nbsp; 18+
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
                    Livewire.dispatch('alpine-guest-bet-slip-updated', {
                        selections: Object.fromEntries(Object.entries(this.selections))
                    });
                    window.dispatchEvent(new CustomEvent('bet-slip-updated'));
                },

                remove(eventId) {
                    delete this.selections[eventId];
                    Livewire.dispatch('alpine-guest-bet-slip-updated', {
                        selections: Object.fromEntries(Object.entries(this.selections))
                    });
                },

                clear() {
                    this.selections = {};
                    Livewire.dispatch('alpine-guest-bet-slip-updated', { selections: {} });
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

            const SB_MARKET_LABELS = {
                h2h:'Match Winner', totals:'Over / Under', spreads:'Handicap',
                btts:'Both Teams To Score', draw_no_bet:'Draw No Bet', double_chance:'Double Chance',
                alternate_spreads:'Alt. Handicap', alternate_totals:'Alt. Over/Under',
                team_totals:'Team Totals', h2h_3_way:'Three-Way',
                h2h_h1:'1st Half Result', h2h_3_way_h1:'1st Half 3-Way', totals_h1:'1st Half Over/Under',
            };
            const SB_MARKET_TABS = {
                h2h:'main', draw_no_bet:'main', double_chance:'main', h2h_lay:'main',
                totals:'goals', btts:'goals',
                h2h_h1:'halftime', h2h_3_way_h1:'halftime', totals_h1:'halftime',
            };
            const SB_TAB_LABELS = { all:'All Markets', main:'Main', goals:'Goals', halftime:'Half Time', other:'Other' };
            const SB_GROUP_ICONS = { Football:'⚽', Basketball:'🏀', Boxing:'🥊' };
            const SB_SPORT_ICONS = {
                'EPL':'🏴󠁧󠁢󠁥󠁮󠁧󠁿','La Liga':'🇪🇸','Bundesliga':'🇩🇪','Serie A':'🇮🇹',
                'Ligue 1':'🇫🇷','UEFA Champions League':'⭐','MLS':'🇺🇸','NBA':'🏀','WNBA':'🏀','Boxing':'🥊',
            };

            Alpine.store('sportsbook', {
                loaded: false,
                sports: {},
                events: {},
                selectedSport: 'soccer_epl',
                generatedAt: null,
                modalOpen: false,
                modalEvent: null,
                modalMarkets: {},
                modalLoadingMore: false,
                modalActiveTab: 'all',
                eventTab: 'highlights',
                upcomingSort: 'time',

                async init() {
                    await this.loadData();
                    setInterval(() => this.loadData(), 900_000);
                },

                async loadData() {
                    try {
                        const res = await fetch('/sportsbook/data');
                        const data = await res.json();
                        this.generatedAt = data.generated_at;
                        const grouped = {};
                        this.events = {};
                        for (const sport of (data.sports ?? [])) {
                            this.events[sport.sport_key] = sport.events ?? [];
                            const group = sport.display_group ?? sport.sport_group;
                            (grouped[group] ??= []).push({
                                key: sport.sport_key,
                                title: sport.display_name ?? sport.sport_title,
                                group, priority: sport.priority ?? 99,
                            });
                        }
                        for (const g in grouped) grouped[g].sort((a, b) => a.priority - b.priority);
                        this.sports = grouped;
                        this.loaded = true;
                    } catch (e) { console.error('[Sportsbook] load failed', e); }
                },

                selectSport(key) { this.selectedSport = key; },
                getEvents() { return this.events[this.selectedSport] ?? []; },
                getSportTitle() {
                    for (const items of Object.values(this.sports)) {
                        const s = items.find(s => s.key === this.selectedSport);
                        if (s) return s.title;
                    }
                    return this.selectedSport.replace(/_/g, ' ');
                },
                getSelectedGroup() {
                    for (const [g, items] of Object.entries(this.sports)) {
                        if (items.some(s => s.key === this.selectedSport)) return g;
                    }
                    return Object.keys(this.sports)[0] ?? '';
                },
                groupIcon(g) { return SB_GROUP_ICONS[g] ?? '🎮'; },
                sportIcon(title, g) { return SB_SPORT_ICONS[title] ?? SB_GROUP_ICONS[g] ?? '🎮'; },

                _eat(commenceTime) {
                    const EAT = 3 * 3600_000;
                    const dt = new Date(new Date(commenceTime).getTime() + EAT);
                    const now = new Date(Date.now() + EAT);
                    const dtD = dt.toISOString().slice(0,10);
                    const nowD = now.toISOString().slice(0,10);
                    const tmr = new Date(now); tmr.setUTCDate(tmr.getUTCDate() + 1);
                    const hh = String(dt.getUTCHours()).padStart(2,'0');
                    const mm = String(dt.getUTCMinutes()).padStart(2,'0');
                    return { dt, dtD, nowD, tmrD: tmr.toISOString().slice(0,10), time: `${hh}:${mm}` };
                },
                formatTime(ct) {
                    if (!ct) return '';
                    const { dt, dtD, nowD, tmrD, time } = this._eat(ct);
                    if (dtD === nowD) return `Today ${time}`;
                    if (dtD === tmrD) return `Tomorrow ${time}`;
                    const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                    const d = String(dt.getUTCDate()).padStart(2,'0');
                    const m = String(dt.getUTCMonth()+1).padStart(2,'0');
                    return `${days[dt.getUTCDay()]} ${d}/${m} ${time}`;
                },
                formatModalTime(ct) {
                    if (!ct) return '';
                    const { dt, dtD, nowD, tmrD, time } = this._eat(ct);
                    if (dtD === nowD) return `Today ${time}`;
                    if (dtD === tmrD) return `Tomorrow ${time}`;
                    const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    return `${days[dt.getUTCDay()]} ${dt.getUTCDate()} ${months[dt.getUTCMonth()]}, ${time}`;
                },
                _gameDuration(sportKey) {
                    if (!sportKey) return 105 * 60000;
                    if (sportKey.startsWith('basketball')) return 180 * 60000;
                    if (sportKey === 'boxing') return 90 * 60000;
                    return 105 * 60000;
                },
                isExpired(ct, sportKey) {
                    if (!ct) return true;
                    return new Date(ct).getTime() + this._gameDuration(sportKey) < Date.now();
                },
                isLive(ct, sportKey) {
                    if (!ct) return false;
                    const start = new Date(ct).getTime();
                    const now = Date.now();
                    return start < now && (start + this._gameDuration(sportKey) > now);
                },
                getAllEventsFlat() {
                    const all = [];
                    for (const events of Object.values(this.events)) {
                        for (const ev of events) all.push(ev);
                    }
                    return all;
                },
                getHighlightEvents() {
                    const bySport = {};
                    for (const ev of this.getAllEventsFlat()) {
                        if (this.isExpired(ev.commence_time, ev.sport_key)) continue;
                        (bySport[ev.sport_key] ??= []).push(ev);
                    }
                    for (const key of Object.keys(bySport)) {
                        bySport[key].sort((a, b) => new Date(a.commence_time) - new Date(b.commence_time));
                        bySport[key] = bySport[key].slice(0, 3);
                    }
                    const result = Object.values(bySport).flat();
                    result.sort((a, b) => {
                        const aL = this.isLive(a.commence_time, a.sport_key) ? 0 : 1;
                        const bL = this.isLive(b.commence_time, b.sport_key) ? 0 : 1;
                        if (aL !== bL) return aL - bL;
                        return new Date(a.commence_time) - new Date(b.commence_time);
                    });
                    return result;
                },
                getUpcomingEvents() {
                    const all = this.getAllEventsFlat().filter(ev => !this.isExpired(ev.commence_time, ev.sport_key));
                    if (this.upcomingSort === 'league') {
                        const priorityMap = {};
                        for (const items of Object.values(this.sports)) {
                            for (const s of items) priorityMap[s.key] = s.priority;
                        }
                        all.sort((a, b) => {
                            const pa = priorityMap[a.sport_key] ?? 99;
                            const pb = priorityMap[b.sport_key] ?? 99;
                            if (pa !== pb) return pa - pb;
                            return new Date(a.commence_time) - new Date(b.commence_time);
                        });
                    } else {
                        all.sort((a, b) => new Date(a.commence_time) - new Date(b.commence_time));
                    }
                    return all;
                },
                getLiveEvents() {
                    return this.getAllEventsFlat()
                        .filter(ev => this.isLive(ev.commence_time, ev.sport_key))
                        .sort((a, b) => new Date(a.commence_time) - new Date(b.commence_time));
                },
                getTabEvents() {
                    if (this.eventTab === 'live') return this.getLiveEvents();
                    if (this.eventTab === 'upcoming') return this.getUpcomingEvents();
                    return this.getHighlightEvents();
                },
                setEventTab(tab) { this.eventTab = tab; },
                setUpcomingSort(sort) { this.upcomingSort = sort; },
                getListTitle() {
                    if (this.eventTab === 'live') return 'Live Now';
                    if (this.eventTab === 'upcoming') return 'Upcoming';
                    return 'Highlights';
                },
                getEmptyMessage() {
                    if (this.eventTab === 'live') return 'No live events right now.';
                    if (this.eventTab === 'highlights') return 'No highlights available.';
                    return 'No upcoming events available.';
                },

                h2h(event) {
                    const m = event.markets?.h2h ?? {};
                    const outcomes = m.outcomes ?? [];
                    let home = null, draw = null, away = null;
                    for (const o of outcomes) {
                        if (o.name.toLowerCase() === 'draw') draw = o;
                        else if (!home) home = o;
                        else away = o;
                    }
                    return { title: m.title ?? '3 Way', home, draw, away };
                },
                spreads(event) {
                    const m = event.markets?.spreads ?? {};
                    const outcomes = m.outcomes ?? [];
                    const byTeam = {};
                    for (const o of outcomes) { if (!byTeam[o.name]) byTeam[o.name] = o; }
                    return { title: m.title ?? 'Handicap', home: byTeam[event.home_team] ?? null, away: byTeam[event.away_team] ?? null };
                },
                totals(event) {
                    const m = event.markets?.totals ?? {};
                    const outcomes = m.outcomes ?? [];
                    const byPoint = {};
                    for (const o of outcomes) {
                        const k = `${o.point??0}_${o.name.toLowerCase().includes('over')?'o':'u'}`;
                        if (!byPoint[k]) byPoint[k] = o;
                    }
                    const sorted = Object.values(byPoint).sort((a, b) => {
                        if ((a.point??0) !== (b.point??0)) return (a.point??0) - (b.point??0);
                        return (a.name.toLowerCase().includes('over')?0:1) - (b.name.toLowerCase().includes('over')?0:1);
                    });
                    return {
                        title: m.title ?? 'Over/Under',
                        over: sorted.find(o => o.name.toLowerCase().includes('over')) ?? null,
                        under: sorted.find(o => o.name.toLowerCase().includes('under')) ?? null,
                    };
                },
                pt(p) { return p == null ? '' : (p > 0 ? `+${p}` : `${p}`); },

                openModal(event) {
                    this.modalEvent = event;
                    this.modalMarkets = event.markets ?? {};
                    this.modalOpen = true;
                    this.modalActiveTab = 'all';
                    this.modalLoadingMore = true;
                    this._fetchMoreMarkets(event.id);
                },
                async _fetchMoreMarkets(eventId) {
                    try {
                        const res = await fetch(`/sportsbook/event-odds/${this.selectedSport}/${eventId}`);
                        const data = await res.json();
                        if (data.markets) this.modalMarkets = { ...this.modalMarkets, ...data.markets };
                    } catch (e) {}
                    this.modalLoadingMore = false;
                },
                closeModal() {
                    this.modalOpen = false; this.modalEvent = null;
                    this.modalMarkets = {}; this.modalLoadingMore = false;
                },
                setModalTab(tab) { this.modalActiveTab = tab; },
                modalTabs() {
                    const tabs = ['all'];
                    for (const [key, data] of Object.entries(this.modalMarkets)) {
                        if (!(data?.outcomes ?? []).length) continue;
                        const cat = SB_MARKET_TABS[key] ?? 'other';
                        if (!tabs.includes(cat)) tabs.push(cat);
                    }
                    return tabs;
                },
                modalTabLabel(tab) { return SB_TAB_LABELS[tab] ?? (tab.charAt(0).toUpperCase()+tab.slice(1)); },
                marketLabel(key, title) { return title || SB_MARKET_LABELS[key] || key.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()); },
                marketTabCat(key) { return SB_MARKET_TABS[key] ?? 'other'; },
                modalMarketsFiltered() {
                    return Object.entries(this.modalMarkets).filter(([key, data]) => {
                        if (!(data?.outcomes ?? []).length) return false;
                        return this.modalActiveTab === 'all' || (SB_MARKET_TABS[key] ?? 'other') === this.modalActiveTab;
                    });
                },
                addModalToBetSlip(outcome, marketKey, marketTitle) {
                    const ev = this.modalEvent;
                    if (!ev) return;
                    const name = outcome.description ? `${outcome.name} ${outcome.description}` : outcome.name;
                    Alpine.store('betSlip').add(ev.id, ev.id+'_'+marketKey, name, outcome.price, ev.home_team, ev.away_team, marketKey, this.marketLabel(marketKey, marketTitle), ev.commence_time, this.isLive(ev.commence_time, ev.sport_key));
                },
                isModalOutcomeSelected(outcome, marketKey) {
                    const ev = this.modalEvent;
                    if (!ev) return false;
                    const name = outcome.description ? `${outcome.name} ${outcome.description}` : outcome.name;
                    return Alpine.store('betSlip').isSelected(ev.id, name, marketKey);
                },
            });
        });
        </script>
    </body>
</html>
