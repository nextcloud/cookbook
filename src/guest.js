/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

import { generateUrl } from "@nextcloud/router"

import Vue from "vue"
import store from "./store"

import AppInvalidGuest from "./components/AppInvalidGuest.vue"

// eslint-disable-next-line camelcase,no-undef
if(__webpack_use_dev_server__ || false){
    // eslint-disable-next-line camelcase,no-undef
    __webpack_public_path__ = 'http://127.0.0.1:3000/apps/nextcloud-cookbook/js/'
}

// eslint-disable-next-line func-names, import/newline-after-import
;(function (OC, window) {
    // Fetch Nextcloud nonce identifier for dynamic script loading
    // eslint-disable-next-line camelcase,no-undef
    __webpack_nonce__ = btoa(OC.requestToken)

    // eslint-disable-next-line no-param-reassign
    window.baseUrl = generateUrl("apps/cookbook")

    // Also make the injections available in Vue components
    Vue.prototype.$window = window
    Vue.prototype.OC = OC

    // Pass translation engine to Vue
    Vue.prototype.t = window.t

    // Start the app once document is done loading
    document.addEventListener("DOMContentLoaded", () => {
        const App = Vue.extend(AppInvalidGuest)
        new App({
            store,
            // router,
        }).$mount("#content")
    })
})(OC, window)
