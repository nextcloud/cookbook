/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

/// <reference types="@nextcloud/typings" />

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import { useLegacyStore } from './store';

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

const app = createApp({
	extends: AppInvalidGuest,
	beforeCreate() {
		const legacyStore = useLegacyStore();
		legacyStore.refreshConfig();
		legacyStore.initializeStore();
	},
});

// TODO Check devmode for debugging
app.config.performance = import.meta.env.MODE === 'development';

// Also make the injections available in Vue components
app.config.globalProperties.OC = window.OC;

// Pass translation engine to Vue
app.config.globalProperties.t = window.t;
app.config.globalProperties.n = window.n;

const pinia = createPinia();
app.use(pinia);

// Start the app once document is done loading
app.mount('#content');
