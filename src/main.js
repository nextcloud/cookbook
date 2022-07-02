/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

// Markdown
import VueShowdown from "vue-showdown"

import { generateUrl } from "@nextcloud/router"

import Vue from "vue"

import api from "cookbook/js/api-interface"
import helpers from "cookbook/js/helper"

import router from "./router"
import store from "./store"

import AppMain from "./components/AppMain.vue"

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
    api.init(window.baseUrl)

    helpers.useRouter(router)

    // Check if two routes point to the same component but have different content
    // eslint-disable-next-line no-param-reassign
    window.shouldReloadContent = helpers.shouldReloadContent

    // Check if the two urls point to the same item instance
    // eslint-disable-next-line no-param-reassign
    window.isSameItemInstance = helpers.isSameItemInstance

    // A simple function to sanitize HTML tags
    // eslint-disable-next-line no-param-reassign
    window.escapeHTML = helpers.escapeHTML

    // Fix the decimal separator for languages that use a comma instead of dot
    // eslint-disable-next-line no-param-reassign
    window.fixDecimalSeparator = helpers.fixDecimalSeparator

    // This will replace the PHP function nl2br in Vue components
    // eslint-disable-next-line no-param-reassign
    window.nl2br = helpers.nl2br

    // A simple function that converts a MySQL datetime into a timestamp.
    // eslint-disable-next-line no-param-reassign
    window.getTimestamp = helpers.getTimestamp

    // Push a new URL to the router, essentially navigating to that page.
    // eslint-disable-next-line no-param-reassign
    window.goTo = helpers.goTo

    // Notify the user if notifications are allowed
    // eslint-disable-next-line no-param-reassign
    window.notify = helpers.notify

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
    document.addEventListener("DOMContentLoaded", () => {
        const App = Vue.extend(AppMain)
        new App({
            store,
            router,
            beforeCreate() {
                this.$store.commit("initializeStore")
            },
        }).$mount("#content")
    })
})(OC, window)
