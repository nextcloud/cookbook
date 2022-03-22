/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

import { generateUrl } from "@nextcloud/router"

import { createApp } from "vue"
import store from "./store"

import AppInvalidGuest from "./components/AppInvalidGuest.vue"

// eslint-disable-next-line camelcase,no-undef
if (__webpack_use_dev_server__ || false) {
    // eslint-disable-next-line camelcase,no-undef
    __webpack_public_path__ =
        "http://127.0.0.1:3000/apps/nextcloud-cookbook/js/"
}

// eslint-disable-next-line func-names, import/newline-after-import
;(function (OC, window) {
    // Fetch Nextcloud nonce identifier for dynamic script loading
    // eslint-disable-next-line camelcase,no-undef
    __webpack_nonce__ = btoa(OC.requestToken)

    // eslint-disable-next-line no-param-reassign
    window.baseUrl = generateUrl("apps/cookbook")

    const app = createApp(AppInvalidGuest)

    // Also make the injections available in Vue components
    app.config.globalProperties.$window = window
    app.config.globalProperties.OC = OC

    // Pass translation engine to Vue
    app.config.globalProperties.t = window.t

    app.use(store)

    // Start the app once document is done loading
    // document.addEventListener("DOMContentLoaded", () => {
    //     const App = Vue.extend(AppInvalidGuest)
    //     new App({
    //         store,
    //         // router,
    //     }).$mount("#content")
    // })

    app.mount('#content')
})(OC, window)
