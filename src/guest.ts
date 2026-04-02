/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

/// <reference types="@nextcloud/typings" />

import Vue from 'vue';

import { useStore } from './store';

import AppInvalidGuest from './components/AppInvalidGuest.vue';

declare global {
	interface Window {
		OC:
			| Nextcloud.v16.OC
			| Nextcloud.v17.OC
			| Nextcloud.v18.OC
			| Nextcloud.v19.OC
			| Nextcloud.v20.OC;
		n: string;
		t: string;
	}
}

Vue.config.devtools = import.meta.env.MODE === 'development';

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
