/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

import Vue from 'vue';

import { useStore } from './store';

import AppInvalidGuest from './components/AppInvalidGuest.vue';

declare global {
	interface Window {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		OC: Nextcloud.v16.OC | Nextcloud.v17.OC | Nextcloud.v18.OC | Nextcloud.v19.OC | Nextcloud.v20.OC;
		n: string;
		t: string;
	}
}

const isDevServer = process.env.WEBPACK_DEV_SERVER;

// eslint-disable-next-line camelcase,no-undef
if (isDevServer || false) {
	// eslint-disable-next-line camelcase,no-undef
	__webpack_public_path__ = 'http://127.0.0.1:3000/apps/cookbook/js/';
}

// Fetch Nextcloud nonce identifier for dynamic script loading
// eslint-disable-next-line camelcase,no-undef
__webpack_nonce__ = btoa(window.OC.requestToken);

// Also make the injections available in Vue components
Vue.prototype.OC = window.OC;

// Pass translation engine to Vue
Vue.prototype.t = window.t;
Vue.prototype.n = window.n;

const store = useStore();

store.dispatch('refreshConfig');

// Start the app once document is done loading
const App = Vue.extend(AppInvalidGuest);
new App({
	store,
	// router,
}).$mount('#content');
