import './alpine/bottom-nav.js';
import { initInstall } from './pwa/install.js';
import { initPush } from './pwa/push.js';
import { registerServiceWorker } from './pwa/register-sw.js';
// import './alpine/sportsbook.js';
// import './alpine/guest-sportsbook.js';

registerServiceWorker();
initInstall();
initPush();
