/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

/// <reference types="@nextcloud/typings" />

// Markdown
import VueShowdown from 'vue-showdown';

import { createApp } from 'vue';

import * as ModalDialogs from 'vue-modal-dialogs';

import { createPinia, PiniaVuePlugin } from 'pinia';

import helpers from './js/helper';
import setupLogging from './js/logging';

import router from './router';
import { useLegacyStore } from './store';

import AppMain from './components/AppMain.vue';

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
		escapeHTML(text: string): string;
	}
}

declare module 'vue/types/vue' {
	export interface VueConstructor<V extends Vue = Vue> {
		$log: {
			debug(...args: (string | object)[]): void;
			info(...args: (string | object)[]): void;
			warn(...args: (string | object)[]): void;
			error(...args: (string | object)[]): void;
			fatal(...args: (string | object)[]): void;
		};
	}
}

const app = createApp({
	extends: AppMain,
	beforeCreate() {
		const legacyStore = useLegacyStore();
		legacyStore.refreshConfig();
		legacyStore.initializeStore();
	},
});

// TODO Check dev mode for debugging
app.config.performance = import.meta.env.MODE === 'development';

// Register helper functions
helpers.useRouter(router);

// A simple function to sanitize HTML tags
// eslint-disable-next-line no-param-reassign
window.escapeHTML = helpers.escapeHTML;

// Also make the injections available in Vue components
app.config.globalProperties.$window = window;
app.config.globalProperties.OC = window.OC;

// Markdown for Vue
app.use(VueShowdown, {
	// set default flavor for Markdown
	flavor: 'vanilla',
});

// TODO: Equivalent library for Vue3 when we make that transition:
// https://github.com/rlemaigre/vue3-promise-dialog
// TODO Vue.use(ModalDialogs);

setupLogging(app);

// Pass translation engine to Vue
app.config.globalProperties.t = window.t;
app.config.globalProperties.n = window.n;

app.use(PiniaVuePlugin);
const pinia = createPinia();

// Start the app once document is done loading
app.$log.info('Main is done. Creating App.');

// const App = Vue.extend(AppMain);
// new App({
// 	router,
// 	pinia,
// 	beforeCreate() {
// 		const legacyStore = useLegacyStore();
// 		legacyStore.refreshConfig();
// 		legacyStore.initializeStore();
// 	},
// });

app.$mount('#content');
