/**
 * Registers the Kadi service worker (public/sw.js, scope "/").
 *
 * Fails silently: the site must work identically without a service worker.
 * When a new worker takes over an already-controlled page, a "pwa:updated"
 * event is dispatched so the UI can offer a refresh. Nothing reloads by itself,
 * so a form being filled in is never interrupted.
 */
export function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const register = () => {
        navigator.serviceWorker
            .register('/sw.js', { scope: '/', updateViaCache: 'none' })
            .then((registration) => {
                registration.addEventListener('updatefound', () => {
                    const incoming = registration.installing;

                    if (!incoming) {
                        return;
                    }

                    incoming.addEventListener('statechange', () => {
                        if (incoming.state === 'installed' && navigator.serviceWorker.controller) {
                            window.dispatchEvent(new CustomEvent('pwa:updated'));
                        }
                    });
                });
            })
            .catch((error) => console.debug('[pwa] service worker registration failed', error));
    };

    if (document.readyState === 'complete') {
        register();
    } else {
        window.addEventListener('load', register, { once: true });
    }
}
