@unless (request()->cookie('kadi_preloaded'))
    <div id="kadi-preloader" class="kadi-preloader" role="status" aria-live="polite" aria-label="Loading Kadi Kings">
        <span class="casino-floater kadi-preloader__floater" style="top:15%; left:10%; font-size:2rem; animation-duration:7s;">♠</span>
        <span class="casino-floater kadi-preloader__floater" style="top:72%; left:16%; font-size:1.5rem; animation-duration:9s; animation-delay:1s;">♥</span>
        <span class="casino-floater kadi-preloader__floater" style="top:18%; right:12%; font-size:1.75rem; animation-duration:8s; animation-delay:.5s;">♦</span>
        <span class="casino-floater kadi-preloader__floater" style="top:68%; right:9%; font-size:2.25rem; animation-duration:10s; animation-delay:1.5s;">♣</span>

        <div class="kadi-preloader__inner">
            <div class="kadi-preloader__cards" aria-hidden="true">
                <span class="kadi-preloader__card">K</span>
                <span class="kadi-preloader__card">A</span>
                <span class="kadi-preloader__card">D</span>
                <span class="kadi-preloader__card">I</span>
            </div>

            <div class="kadi-preloader__suit" aria-hidden="true">♠</div>

            <div class="kadi-preloader__track" aria-hidden="true"></div>

            <p class="kadi-preloader__text shimmer-text" data-kadi-loading-text>Shuffling the deck&hellip;</p>
        </div>
    </div>

    <script>
        (function () {
            var el = document.getElementById('kadi-preloader');
            if (!el) return;

            var messages = ['Shuffling the deck…', 'Dealing your cards…', 'Setting the table…', 'Almost ready…'];
            var idx = 0;
            var textEl = el.querySelector('[data-kadi-loading-text]');
            var textTimer = setInterval(function () {
                idx = (idx + 1) % messages.length;
                if (textEl) textEl.textContent = messages[idx];
            }, 1400);

            // Keep it on screen for at least this long so the deal animation
            // always gets to finish, even on a fast/cached load.
            var MIN_VISIBLE_MS = 1100;
            var startedAt = Date.now();

            function finish() {
                var remaining = MIN_VISIBLE_MS - (Date.now() - startedAt);
                setTimeout(function () {
                    el.classList.add('is-hidden');
                    clearInterval(textTimer);
                    // Session-length cookie: skips the preloader on subsequent
                    // wire:navigate hops and reloads until the browser session ends.
                    document.cookie = 'kadi_preloaded=1; path=/';
                    setTimeout(function () { el.remove(); }, 600);
                }, Math.max(remaining, 0));
            }

            if (document.readyState === 'complete') {
                finish();
            } else {
                window.addEventListener('load', finish);
            }
        })();
    </script>
@endunless
