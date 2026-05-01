/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

/// <reference types="@nextcloud/typings" />

// Markdown
import VueShowdown from 'vue-showdown';

import { createApp, h } from 'vue';

// TODO
// import * as ModalDialogs from 'vue-modal-dialogs';

import { createPinia } from 'pinia';

import helpers from './js/helper';
import setupLogging from './js/logging';

import { createMainRouter, getRouter } from './router';
import { useLegacyStore } from './store';
import { setApp as setAppInApiInterface } from 'cookbook/js/api-interface';

import AppMain from './components/AppMain.vue';

console.log('Mode: ', import.meta.env.MODE);


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

const app = createApp(AppMain);

/*const app = createApp({
	extends: AppMain,
	name: 'RootApp',
	beforeCreate() {
		const legacyStore = useLegacyStore();
		legacyStore.refreshConfig();
		legacyStore.initializeStore();
	},
});*/

const app2 = createApp({
	// template: '<div>hello</div>',
	render() {
		return h('div', 'hello');
	}
});

// TODO Check dev mode for debugging
app.config.performance = import.meta.env.MODE === 'development';

createMainRouter();
const router = getRouter();
app.use(router);

// Register helper functions
helpers.useRouter(router);

// A simple function to sanitize HTML tags
 
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
setAppInApiInterface(app);

// Pass translation engine to Vue
app.config.globalProperties.t = window.t;
app.config.globalProperties.n = window.n;

const pinia = createPinia();
app.use(pinia);

// Only create the store after `use`ing the pinia store
const legacyStore = useLegacyStore();
legacyStore.refreshConfig();
legacyStore.initializeStore();

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

app2.use(pinia);
app2.use(router);

app.mount('#content');

