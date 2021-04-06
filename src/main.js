/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * @license AGPL3 or later
 */

// Markdown
import VueShowdown from "vue-showdown"
import Editor from "v-markdown-editor"
import "v-markdown-editor/dist/v-markdown-editor.css"

import Vue from "vue"
import router from "./router"
import store from "./store"

import AppMain from "./components/AppMain.vue"

// eslint-disable-next-line func-names, import/newline-after-import
;(function (OC, window) {
    // Fetch Nextcloud nonce identifier for dynamic script loading
    // eslint-disable-next-line camelcase,no-undef
    __webpack_nonce__ = btoa(OC.requestToken)

    // eslint-disable-next-line no-param-reassign
    window.baseUrl = OC.generateUrl("apps/cookbook")

    // Check if two routes point to the same component but have different content
    // eslint-disable-next-line no-param-reassign
    window.shouldReloadContent = function shouldReloadContent(url1, url2) {
        if (url1 === url2) {
            return false // Obviously should not if both routes are the same
        }

        const comps1 = url1.split("/")
        const comps2 = url2.split("/")

        if (comps1.length < 2 || comps2.length < 2) {
            return false // Just a failsafe, this should never happen
        }

        // The route structure is as follows:
        // - /{item}/:id        View
        // - /{item}/:id/edit   Edit
        // - /{item}/create     Create
        // If the items are different, then the router automatically handles
        // component loading: do not manually reload
        if (comps1[1] !== comps2[1]) {
            return false
        }

        // If one of the routes is edit and the other is not
        if (comps1.length !== comps2.length) {
            // Only reload if changing from edit to create
            return comps1.pop() === "create" || comps2.pop() === "create"
        }
        if (comps1.pop() === "create") {
            // But, if we are moving from create to view, do not reload
            // the create component
            return false
        }

        // Only options left are that both of the routes are edit or view,
        // but not identical, or that we're moving from view to create
        // -> reload view
        return true
    }

    // Check if the two urls point to the same item instance
    // eslint-disable-next-line no-param-reassign
    window.isSameItemInstance = function isSameItemInstance(url1, url2) {
        if (url1 === url2) {
            return true // Obviously true if the routes are the same
        }
        const comps1 = url1.split("/")
        const comps2 = url2.split("/")
        if (comps1.length < 2 || comps2.length < 2) {
            return false // Just a failsafe, this should never happen
        }
        // If the items are different, then the item instance cannot be
        // the same either
        if (comps1[1] !== comps2[1]) {
            return false
        }
        if (comps1.length < 3 || comps2.length < 3) {
            // ID is the third url component, so can't be the same instance if
            // either of the urls have less than three components
            return false
        }
        return comps1[2] === comps2[2]
    }

    // A simple function to sanitize HTML tags
    // eslint-disable-next-line no-param-reassign
    window.escapeHTML = function escapeHTML(text) {
        return text.replace(
            /["&'<>]/g,
            (a) =>
                ({
                    "&": "&amp;",
                    '"': "&quot;",
                    "'": "&apos;",
                    "<": "&lt;",
                    ">": "&gt;",
                }[a])
        )
    }

    // Fix the decimal separator for languages that use a comma instead of dot
    // eslint-disable-next-line no-param-reassign
    window.fixDecimalSeparator = function fixDecimalSeparator(value, io) {
        // value is the string value of the number to process
        // io is either 'i' as in input or 'o' as in output
        if (!value) {
            return ""
        }
        if (io === "i") {
            // Check if it's an American number where a comma precedes a dot
            //  e.g. 12,500.25
            if (value.indexOf(".") > value.indexOf(",")) {
                return value.replace(",", "")
            }
            return value.replace(",", ".")
        }
        if (io === "o") {
            return value.toString().replace(".", ",")
        }
        return ""
    }

    // This will replace the PHP function nl2br in Vue components
    // eslint-disable-next-line no-param-reassign
    window.nl2br = function nl2br(text) {
        return text.replace(/\n/g, "<br />")
    }

    // A simple function that converts a MySQL datetime into a timestamp.
    // eslint-disable-next-line no-param-reassign
    window.getTimestamp = function getTimestamp(date) {
        if (date) {
            return new Date(date)
        }
        return null
    }

    // Push a new URL to the router, essentially navigating to that page.
    // eslint-disable-next-line no-param-reassign
    window.goTo = function goTo(url) {
        router.push(url)
    }

    // Notify the user if notifications are allowed
    // eslint-disable-next-line no-param-reassign
    window.notify = function notify(title, options) {
        if (!("Notification" in window)) {
            return
        }
        if (Notification.permission === "granted") {
            // eslint-disable-next-line no-unused-vars
            const notification = new Notification(title, options)
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission((permission) => {
                if (!("permission" in Notification)) {
                    Notification.permission = permission
                }
                if (permission === "granted") {
                    // eslint-disable-next-line no-unused-vars
                    const notification = new Notification(title, options)
                } else {
                    // eslint-disable-next-line no-alert
                    alert(title)
                }
            })
        }
    }

    // Also make the injections available in Vue components
    Vue.prototype.$window = window
    Vue.prototype.OC = OC

    // Markdown for Vue
    Vue.use(VueShowdown, {
        // set default flavor for Markdown
        flavor: "vanilla",
    })
    Vue.use(Editor)

    // Pass translation engine to Vue
    Vue.prototype.t = window.t

    // Start the app once document is done loading
    document.addEventListener("DOMContentLoaded", () => {
        const App = Vue.extend(AppMain)
        new App({
            store,
            router,
            beforeCreate() {
                this.$store.commit('initializeStore')
            },
        }).$mount("#content")
    })
})(OC, window)
