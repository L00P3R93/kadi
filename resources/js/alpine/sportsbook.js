// Sportsbook bet-slip store commented out (client focus: Kadi only)

document.addEventListener('alpine:init', () => {
    Alpine.store('betSlip', {
        selections: {},

        add(eventId, selectionKey, team, price, homeTeam, awayTeam, marketKey, marketLabel, commenceTime, isLive) {
            const existing = this.selections[eventId];
            if (existing && existing.team === team && existing.marketKey === (marketKey || 'h2h')) {
                delete this.selections[eventId];
            } else {
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
        isLive(ct) { return ct && new Date(ct) < new Date(); },

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
            Alpine.store('betSlip').add(ev.id, ev.id+'_'+marketKey, name, outcome.price, ev.home_team, ev.away_team, marketKey, this.marketLabel(marketKey, marketTitle), ev.commence_time, this.isLive(ev.commence_time));
        },
        isModalOutcomeSelected(outcome, marketKey) {
            const ev = this.modalEvent;
            if (!ev) return false;
            const name = outcome.description ? `${outcome.name} ${outcome.description}` : outcome.name;
            return Alpine.store('betSlip').isSelected(ev.id, name, marketKey);
        },
    });
});
