/**
 * Web push opt-in for signed-in players.
 *
 * Exposes one Alpine store, `pwaPush`, that the notifications toggle reads. Everything runs
 * from user gestures (never on page load) except silent housekeeping that needs no prompt:
 * re-syncing an existing subscription with the server, and dropping one that belongs to a
 * different account.
 *
 * States: checking | unsupported | ios-needs-install | default | granted-unsubscribed |
 *         granted-subscribed | denied     (guests never leave "checking": nothing runs for them)
 *
 * Shared devices:
 *   - Explicit logout deletes this device's server row first, so the next person never gets your
 *     pushes. The browser subscription is kept, so the same account resumes without a prompt.
 *   - If a different account signs in and a subscription from another account is still on the
 *     device, it is dropped locally.
 *   - Idle-timeout logouts cannot run code, so payloads must stay non-sensitive (see PushMessageNotification).
 */
import { isAndroid, isIOS, isStandalone } from './install.js';

const KEY_OPT_IN = 'pwa.push.optin'; // account marker that opted in on this browser
const KEY_ENDPOINT = 'pwa.push.endpoint'; // endpoint last confirmed with the server
const KEY_SESSION_SYNC = 'pwa.push.synced'; // sessionStorage: re-confirmed once this browser session
const READY_TIMEOUT_MS = 8000;
const LOGOUT_TIMEOUT_MS = 1500;
const SUBSCRIPTIONS_URL = '/push/subscriptions';
const TEST_URL = '/push/test';

const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content || null;
const account = () => meta('pwa-user'); // null for guests
const vapidKey = () => meta('vapid-public-key');

function read(storage, key) {
    try {
        return storage.getItem(key);
    } catch (e) {
        return null;
    }
}

function write(storage, key, value) {
    try {
        storage.setItem(key, value);
    } catch (e) {
        // Blocked storage: the next load simply re-syncs.
    }
}

function forget(storage, ...keys) {
    try {
        keys.forEach((key) => storage.removeItem(key));
    } catch (e) {
        // ignore
    }
}

export function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);

    return Uint8Array.from([...raw].map((char) => char.charCodeAt(0)));
}

/** iOS/iPadOS before 16.4 has no web push at all. iPadOS-as-Mac has no version in its UA: assume modern. */
function iosTooOld() {
    const match = /OS (\d+)[_.](\d+)/.exec(navigator.userAgent || '');

    if (!isIOS() || !match) return false;

    const [major, minor] = [Number(match[1]), Number(match[2])];

    return major < 16 || (major === 16 && minor < 4);
}

// ---------------------------------------------------------------------------
// Store plumbing (same pattern as install.js: values seen before Alpine boots are folded in)
// ---------------------------------------------------------------------------
const pending = { state: 'checking', reason: null, busy: false, error: null, testing: false, testResult: null };

function update(patch) {
    const store = window.Alpine && window.Alpine.store('pwaPush');

    Object.assign(store || pending, patch);
}

const errorFor = (code) => {
    const table = {
        consent: { code, message: 'Please confirm your age and accept the terms first, then try again.', href: '/consent', linkText: 'Review terms' },
        session: { code, message: 'Your session expired. Please sign in again.' },
        unsupported: { code, message: "This browser's notification service isn't supported yet." },
        throttled: { code, message: 'Too many attempts. Please wait a minute and try again.' },
        pushService: { code, message: "Your browser's notification service couldn't be reached. Please try again later." },
        network: { code, message: "We couldn't turn notifications on. Check your connection and try again." },
    };

    return table[code] || table.network;
};

class ServerError extends Error {
    constructor(status) {
        super(`push subscription request failed: ${status}`);
        this.status = status;
    }
}

function describe(error) {
    if (error instanceof ServerError) {
        if (error.status === 403) return errorFor('consent');
        if (error.status === 401 || error.status === 419) return errorFor('session');
        if (error.status === 422) return errorFor('unsupported');
        if (error.status === 429) return errorFor('throttled');

        return errorFor('network');
    }

    // Chromium builds without Google services, or a blocked push service, fail with AbortError.
    if (error && (error.name === 'AbortError' || error.name === 'InvalidStateError')) return errorFor('pushService');

    return errorFor('network');
}

// ---------------------------------------------------------------------------
// Server calls
// ---------------------------------------------------------------------------
function request(method, body, extra = {}, url = SUBSCRIPTIONS_URL) {
    return fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': meta('csrf-token') || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
        ...extra,
    });
}

function payloadFor(subscription) {
    const json = subscription.toJSON();
    const encodings = (window.PushManager && PushManager.supportedContentEncodings) || [];
    const contentEncoding = encodings.includes('aes128gcm') ? 'aes128gcm' : encodings[0];

    return { endpoint: json.endpoint, keys: json.keys, ...(contentEncoding ? { contentEncoding } : {}) };
}

async function syncWithServer(subscription) {
    const response = await request('POST', payloadFor(subscription));

    if (!response.ok) throw new ServerError(response.status);

    write(localStorage, KEY_ENDPOINT, subscription.endpoint);
    write(sessionStorage, KEY_SESSION_SYNC, '1');
}

// ---------------------------------------------------------------------------
// Browser subscription helpers
// ---------------------------------------------------------------------------
function readyRegistration() {
    return Promise.race([
        navigator.serviceWorker.ready,
        new Promise((_, reject) => window.setTimeout(() => reject(new Error('service worker not ready')), READY_TIMEOUT_MS)),
    ]);
}

function usesCurrentKey(subscription) {
    const key = subscription.options && subscription.options.applicationServerKey;
    const wanted = vapidKey();

    if (!key || !wanted) return true; // cannot tell (e.g. some browsers): assume fine

    const have = new Uint8Array(key);
    const want = urlBase64ToUint8Array(wanted);

    return have.length === want.length && have.every((byte, index) => byte === want[index]);
}

function createSubscription(registration) {
    return registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidKey()),
    });
}

/** Non-null when notifications cannot work here, with the state to show. */
function unavailable() {
    if (!account()) return { state: 'checking', reason: 'guest' };
    if (!vapidKey()) return { state: 'unsupported', reason: 'unconfigured' };
    if (iosTooOld()) return { state: 'unsupported', reason: 'ios-old' };
    if (isIOS() && !isStandalone()) return { state: 'ios-needs-install', reason: null };

    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        return { state: 'unsupported', reason: 'browser' };
    }

    return null;
}

// ---------------------------------------------------------------------------
// State detection and silent housekeeping
// ---------------------------------------------------------------------------
let refreshing = null;

function refreshState() {
    refreshing = refreshing || detectState().finally(() => { refreshing = null; });

    return refreshing;
}

async function detectState() {
    const blocked = unavailable();

    if (blocked) {
        update(blocked);

        return;
    }

    let registration;

    try {
        registration = await readyRegistration();
    } catch (e) {
        update({ state: 'unsupported', reason: 'service-worker' });

        return;
    }

    const permission = Notification.permission;

    if (permission === 'denied') return update({ state: 'denied', reason: null });
    if (permission !== 'granted') return update({ state: 'default', reason: null });

    const me = account();
    const optedInAs = read(localStorage, KEY_OPT_IN);
    let subscription = await registration.pushManager.getSubscription();

    // A subscription left by another account on a shared device must not reach this person.
    if (subscription && optedInAs && optedInAs !== me) {
        await subscription.unsubscribe().catch(() => {});
        forget(localStorage, KEY_OPT_IN, KEY_ENDPOINT);
        forget(sessionStorage, KEY_SESSION_SYNC);
        subscription = null;
    }

    // Made with a different VAPID key (keys were rotated): it can never receive our pushes.
    if (subscription && !usesCurrentKey(subscription)) {
        await subscription.unsubscribe().catch(() => {});
        subscription = null;
    }

    const optedIn = read(localStorage, KEY_OPT_IN) === me;

    // The browser dropped it but this account had opted in: re-subscribe silently (no prompt needed).
    if (!subscription && optedIn) {
        try {
            subscription = await createSubscription(registration);
        } catch (e) {
            subscription = null;
        }
    }

    if (!subscription || !optedIn) {
        return update({ state: 'granted-unsubscribed', reason: null });
    }

    update({ state: 'granted-subscribed', reason: null });

    // Confirm with the server when the endpoint changed, and once per browser session (self-healing).
    const needsSync = read(localStorage, KEY_ENDPOINT) !== subscription.endpoint || !read(sessionStorage, KEY_SESSION_SYNC);

    if (needsSync) {
        try {
            await syncWithServer(subscription);
        } catch (e) {
            console.debug('[pwa] push sync failed; will retry on the next load', e);
        }
    }
}

// ---------------------------------------------------------------------------
// User actions (always from a click)
// ---------------------------------------------------------------------------
async function enable() {
    const store = window.Alpine.store('pwaPush');

    if (store.busy) return;

    update({ busy: true, error: null });

    let subscription = null;
    let createdHere = false;

    try {
        const permission = Notification.permission === 'granted' ? 'granted' : await Notification.requestPermission();

        if (permission === 'denied') return update({ state: 'denied' });
        if (permission !== 'granted') return update({ state: 'default' }); // dismissed without choosing

        const registration = await readyRegistration();

        subscription = await registration.pushManager.getSubscription();

        if (subscription && !usesCurrentKey(subscription)) {
            await subscription.unsubscribe();
            subscription = null;
        }

        if (!subscription) {
            subscription = await createSubscription(registration);
            createdHere = true;
        }

        await syncWithServer(subscription);

        write(localStorage, KEY_OPT_IN, account());
        update({ state: 'granted-subscribed' });
    } catch (error) {
        // Keep browser and server consistent: undo a subscription the server never accepted.
        if (createdHere && subscription) await subscription.unsubscribe().catch(() => {});

        update({ error: describe(error) });
        console.debug('[pwa] enabling notifications failed', error);

        await refreshState();
    } finally {
        update({ busy: false });
    }
}

async function disable() {
    const store = window.Alpine.store('pwaPush');

    if (store.busy) return;

    update({ busy: true, error: null });

    try {
        const registration = await readyRegistration();
        const subscription = await registration.pushManager.getSubscription();
        const endpoint = (subscription && subscription.endpoint) || read(localStorage, KEY_ENDPOINT);

        if (subscription) await subscription.unsubscribe().catch(() => {});

        // Best effort: if it fails the push service answers 410 next time and the row is pruned.
        if (endpoint) await request('DELETE', { endpoint }).catch(() => {});

        forget(localStorage, KEY_OPT_IN, KEY_ENDPOINT);
        forget(sessionStorage, KEY_SESSION_SYNC);
        update({ state: 'granted-unsubscribed', testResult: null });
    } catch (error) {
        update({ error: describe(error) });
    } finally {
        update({ busy: false });
    }
}

/**
 * "Send test notification": asks the server to push to this account's own devices
 * (local/staging or admin roles only; the server enforces that) and explains the outcome.
 */
function describeTest(status, data) {
    if (status === 403) return { ok: false, message: "Test notifications aren't available for your account." };
    if (status === 429) return { ok: false, message: 'Please wait a minute before sending another test.' };
    if (status === 401 || status === 419) return { ok: false, message: 'Your session expired. Please sign in again.' };
    if (status >= 400) return { ok: false, message: data.message || "The test couldn't be sent. Please try again." };

    if (!data.devices) return { ok: false, message: 'No device is registered yet. Turn notifications on first.' };

    if (data.delivered > 0) {
        const noun = data.devices === 1 ? 'device' : 'devices'; // agrees with the total, not the delivered count
        const notes = [];

        if (data.expired) notes.push(`${data.expired} expired and ${data.expired === 1 ? 'was' : 'were'} removed`);
        if (data.failed) notes.push(`${data.failed} failed`);

        const message = notes.length
            ? `Sent to ${data.delivered} of ${data.devices} ${noun} (${notes.join(', ')}).`
            : `Sent to ${data.delivered} of ${data.devices} ${noun}. It should appear in a moment.`;

        return { ok: true, message };
    }

    if (data.expired > 0) {
        return { ok: false, message: 'This device had expired and was removed. Turn notifications off and on again.' };
    }

    return { ok: false, message: 'The push service rejected the message. Please try again shortly.' };
}

async function sendTest() {
    const store = window.Alpine.store('pwaPush');

    if (store.testing) return;

    update({ testing: true, testResult: null });

    try {
        const response = await request('POST', {}, {}, TEST_URL);
        const data = await response.json().catch(() => ({}));

        update({ testResult: describeTest(response.status, data) });
    } catch (e) {
        update({ testResult: { ok: false, message: "Couldn't reach the server. Check your connection and try again." } });
    } finally {
        update({ testing: false });
    }
}

// ---------------------------------------------------------------------------
// Logout: remove this device's server row before the session ends
// ---------------------------------------------------------------------------
function onLogoutSubmit(event) {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.dataset.pwaLogoutDone === '1') return;

    let path;

    try {
        path = new URL(form.action, window.location.href).pathname;
    } catch (e) {
        return;
    }

    const endpoint = read(localStorage, KEY_ENDPOINT);

    if (!/\/logout\/?$/.test(path) || !endpoint || !account()) return;

    event.preventDefault();

    // Forget the sync marker so the next login by the same account re-registers this device.
    forget(localStorage, KEY_ENDPOINT);
    forget(sessionStorage, KEY_SESSION_SYNC);

    const finish = () => {
        form.dataset.pwaLogoutDone = '1';

        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            form.submit();
        }
    };

    Promise.race([
        request('DELETE', { endpoint }, { keepalive: true }).catch(() => {}),
        new Promise((resolve) => window.setTimeout(resolve, LOGOUT_TIMEOUT_MS)), // never let logout hang
    ]).then(finish, finish);
}

// ---------------------------------------------------------------------------
// Store + boot
// ---------------------------------------------------------------------------
function createStore() {
    return {
        ...pending,

        get isOn() {
            return this.state === 'granted-subscribed';
        },

        /** Whether the switch can be operated right now. */
        get canToggle() {
            return !this.busy && (this.state === 'default' || this.state === 'granted-unsubscribed' || this.state === 'granted-subscribed');
        },

        get deniedHelp() {
            if (isIOS()) return 'Open the Settings app, choose Notifications, find Kadi and allow notifications.';
            if (isAndroid()) return 'Open your browser or app settings for Kadi, go to Notifications and set it to Allow.';

            return 'Click the lock icon next to the address bar, then set Notifications to Allow.';
        },

        get unsupportedMessage() {
            const messages = {
                unconfigured: "Notifications aren't available right now.",
                'ios-old': 'Notifications need iOS or iPadOS 16.4 or later. Update your device to use them.',
                'service-worker': "Notifications couldn't start on this device. Reload the page and try again.",
            };

            return messages[this.reason] || "This browser doesn't support notifications.";
        },

        toggle() {
            return this.isOn ? disable() : enable();
        },

        enable,
        disable,
        sendTest,
        refresh: refreshState,
    };
}

let started = false;

export function initPush() {
    if (started) return;
    started = true;

    document.addEventListener('alpine:init', () => {
        window.Alpine.store('pwaPush', createStore());
    });

    // Guests have nothing to manage; do no work and register nothing else.
    if (!account()) return;

    document.addEventListener('submit', onLogoutSubmit, true);

    // Housekeeping once the page has loaded (never asks for permission).
    if (document.readyState === 'complete') {
        refreshState();
    } else {
        window.addEventListener('load', refreshState, { once: true });
    }

    // Pick up changes made outside the page: browser/OS settings, or the service worker.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshState();
    });

    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions
            .query({ name: 'notifications' })
            .then((status) => status.addEventListener('change', refreshState))
            .catch(() => {});
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'pushsubscriptionchange') refreshState();
        });
    }
}
