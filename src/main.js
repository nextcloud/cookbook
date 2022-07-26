/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

// Markdown
import moment from "moment"

import Vue from "vue"

import * as ModalDialogs from "vue-modal-dialogs"

import helpers from "cookbook/js/helper"
import setupLogging from "cookbook/js/logging"

import router from "./router"
import store from "./store"

import AppMain from "./components/AppMain.vue"

// eslint-disable-next-line camelcase,no-undef
if (__webpack_use_dev_server__ || false) {
    // eslint-disable-next-line camelcase,no-undef
    __webpack_public_path__ = "http://127.0.0.1:3000/apps/cookbook/js/"
}

// Fetch Nextcloud nonce identifier for dynamic script loading
// eslint-disable-next-line camelcase,no-undef
__webpack_nonce__ = btoa(OC.requestToken)

helpers.useRouter(router)

const locale = document.documentElement
    .getAttribute("data-locale")
    .replace("_", "-")
    .toLowerCase() // `en` is the default locale and cannot be dynamically imported. Will 404
// https://github.com/moment/moment/issues/3624
;(locale === "en"
    ? Promise.resolve()
    : import(`moment/locale/${locale}.js`)
).then(() => moment.locale(locale))

// A simple function to sanitize HTML tags
// eslint-disable-next-line no-param-reassign
window.escapeHTML = helpers.escapeHTML

// Also make the injections available in Vue components
Vue.prototype.$window = window
Vue.prototype.OC = OC

// eslint-disable-next-line no-undef
Vue.prototype.verboseDebugLogging = verboseDebugLogging

// TODO: Equivalent library for Vue3 when we make that transition:
// https://github.com/rlemaigre/vue3-promise-dialog
Vue.use(ModalDialogs)

setupLogging(Vue)

// Pass translation engine to Vue
Vue.prototype.t = window.t

// Start the app once document is done loading
Vue.$log.info("Main is done. Creating App.")
const App = Vue.extend(AppMain)
new App({
    store,
    router,
    beforeCreate() {
        this.$store.commit("initializeStore")
    },
}).$mount("#content")
