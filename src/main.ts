/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

// Markdown
import VueShowdown from 'vue-showdown';

import Vue from 'vue';

import * as ModalDialogs from 'vue-modal-dialogs';

import { linkTo } from '@nextcloud/router';
import helpers from './js/helper';
import navigation from 'cookbook/js/utils/navigation';
import setupLogging from './js/logging';

import router from './router';
import { useStore } from './store';

import AppMain from './components/AppMain.vue';

declare global {
	interface Window {
		// eslint-disable-next-line @typescript-eslint/no-explicit-any
		OC: any;
		n: string;
		t: string;
		escapeHTML(text: string): string;
	}
}

declare module 'vue/types/vue' {
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
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

const isDevServer = process.env.WEBPACK_DEV_SERVER;

// eslint-disable-next-line camelcase,no-undef
if (isDevServer || false) {
	// eslint-disable-next-line camelcase,no-undef
	__webpack_public_path__ = 'http://127.0.0.1:3000/apps/cookbook/js/';
}

// eslint-disable-next-line camelcase,no-undef
__webpack_public_path__ = `${linkTo('cookbook', 'js')}/`;

// Fetch Nextcloud nonce identifier for dynamic script loading
// eslint-disable-next-line camelcase,no-undef
__webpack_nonce__ = btoa(window.OC.requestToken);

helpers.useRouter(router);
navigation.useRouter(router);

// A simple function to sanitize HTML tags
// eslint-disable-next-line no-param-reassign
window.escapeHTML = helpers.escapeHTML;

// Also make the injections available in Vue components
Vue.prototype.$window = window;
Vue.prototype.OC = window.OC;

// Markdown for Vue
Vue.use(VueShowdown, {
	// set default flavor for Markdown
	flavor: 'vanilla',
});

// TODO: Equivalent library for Vue3 when we make that transition:
// https://github.com/rlemaigre/vue3-promise-dialog
Vue.use(ModalDialogs);

setupLogging(Vue);

const store = useStore();
store.dispatch('refreshConfig');

// Pass translation engine to Vue
Vue.prototype.t = window.t;
Vue.prototype.n = window.n;

// Start the app once document is done loading
Vue.$log.info('Main is done. Creating App.');
const App = Vue.extend(AppMain);
new App({
	store,
	router,
	beforeCreate() {
		this.$store.commit('initializeStore');
	},
}).$mount('#content');
