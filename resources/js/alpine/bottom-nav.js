document.addEventListener('alpine:init', () => {
    Alpine.data('bottomNav', () => ({
        hidden: false,
        lastY: 0,
        ticking: false,
        threshold: 8, // px of scroll before reacting — avoids jitter on tiny scrolls
        target: null,

        init() {
            // The dashboard layout scrolls its <main id="app-scroll-container">
            // (body has overflow-hidden there); the guest layout scrolls the
            // window itself. Fall back to window so this works on both.
            this.target = document.getElementById('app-scroll-container') || window;
            this.lastY = this.scrollY();
            this.target.addEventListener('scroll', () => this.handleScroll(), { passive: true });
        },

        scrollY() {
            return this.target === window ? window.scrollY : this.target.scrollTop;
        },

        handleScroll() {
            if (this.ticking) return;
            this.ticking = true;

            requestAnimationFrame(() => {
                const y = this.scrollY();
                const delta = y - this.lastY;

                if (Math.abs(delta) > this.threshold) {
                    // Always stay visible near the top; hide only once the
                    // user is actively scrolling down past that zone.
                    this.hidden = y > 24 && delta > 0;
                    this.lastY = y;
                }

                this.ticking = false;
            });
        },
    }));
});
