/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

import Vue from 'vue';

import { useStore } from './store';

import AppInvalidGuest from './components/AppInvalidGuest.vue';

// eslint-disable-next-line camelcase,no-undef
if (__webpack_use_dev_server__ || false) {
    // eslint-disable-next-line camelcase,no-undef
    __webpack_public_path__ = 'http://127.0.0.1:3000/apps/cookbook/js/';
}

// Fetch Nextcloud nonce identifier for dynamic script loading
// eslint-disable-next-line camelcase,no-undef
__webpack_nonce__ = btoa(OC.requestToken);

// Also make the injections available in Vue components
Vue.prototype.OC = OC;

// Pass translation engine to Vue
Vue.prototype.t = window.t;

const store = useStore();

// Start the app once document is done loading
const App = Vue.extend(AppInvalidGuest);
new App({
    store,
    // router,
}).$mount('#content');
