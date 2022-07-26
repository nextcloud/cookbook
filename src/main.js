/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

// Markdown
import VueShowdown from "vue-showdown"

import Vue from "vue"

import helpers from "cookbook/js/helper"

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

// A simple function to sanitize HTML tags
// eslint-disable-next-line no-param-reassign
window.escapeHTML = helpers.escapeHTML

// Also make the injections available in Vue components
Vue.prototype.$window = window
Vue.prototype.OC = OC

// Markdown for Vue
Vue.use(VueShowdown, {
    // set default flavor for Markdown
    flavor: "vanilla",
})

// Pass translation engine to Vue
Vue.prototype.t = window.t

// Start the app once document is done loading
const App = Vue.extend(AppMain)
new App({
    store,
    router,
    beforeCreate() {
        this.$store.commit("initializeStore")
    },
}).$mount("#content")
