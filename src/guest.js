/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

import Vue from "vue"
import store from "./store"

import AppInvalidGuest from "./components/AppInvalidGuest"
;(function (OC, window) {
    "use strict"

    // Fetch Nextcloud nonce identifier for dynamic script loading
    __webpack_nonce__ = btoa(OC.requestToken)

    window.baseUrl = OC.generateUrl("apps/cookbook")

    // Also make the injections available in Vue components
    Vue.prototype.$window = window
    Vue.prototype.OC = OC

    // Pass translation engine to Vue
    Vue.prototype.t = window.t

    // Start the app once document is done loading
    document.addEventListener("DOMContentLoaded", function (event) {
        const App = Vue.extend(AppInvalidGuest)
        new App({
            store,
            // router,
        }).$mount("#content")
    })
})(OC, window)
