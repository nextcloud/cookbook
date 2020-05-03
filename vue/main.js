/**
 * Nextcloud Cookbook app
 * Vue frontend entry file
 * ---------------------------
 * <insert whatever copyright and license information you want here>
 * @license AGPL3 or later
*/

import Vue from 'vue'
import i18n from './i18n'
import router from './router'
import store from './store'

// Nextcloud seems to favor markdown syntax in some of their core apps.
// To be consistent with this, we could allow markdown styling of the recipe
//  instructions, maybe even the ingredients.
// I have use marked before, but the markdown library to use has to be
//  agreed upon before we implement it.
// import marked from 'marked';

// The convention is to name the main Vue instance 'App', but since this will
//  ultimately be a component of the main app, we'll use the naming convention
//  for child components.
//import AppNavi from './components/AppNavi'
import AppMain from './components/AppMain'

// TODO: Nextcloud will remove support for jQuery in the next major version,
//  we'll have to either ship our own or remove the jQuery dependency!
(function (OC, window, $, undefined) {
    'use strict'
    // Fetch Nextcloud nonce identifier for dynamic script loading
    __webpack_nonce__ = btoa(OC.requestToken)

    // I like to inject a number of global constants and functions into the
    //  window object. You may or may not like this approach, we can discuss
    //  it later if needed.
    window.baseUrl = OC.generateUrl('apps/cookbook')
    // Check if two routes point to the same component but have different content
    window.shouldReloadContent = function(url1, url2) {
        if (url1 === url2) {
            return false // Obviously should not if both routes are the same
        }
        let comps1 = url1.split('/')
        let comps2 = url2.split('/')
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
            if (comps1.pop() === 'create' || comps2.pop() === 'create') {
                return true
            }
            return false
        }
        // Only option left is that both of the routes are edit or view,
        // but not identical -> reload view
        return true
    }
    // Check if the two urls point to the same item instance
    window.isSameItemInstance = function(url1, url2) {
        if (url1 === url2) {
            return true // Obviously true if the routes are the same
        }
        let comps1 = url1.split('/')
        let comps2 = url2.split('/')
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
        if (comps1[2] !== comps2[2]) {
            // Different IDs, not same instance
            return false
        }
        return true
    }
    // A simple function to sanitize HTML tags, although Vue does pretty good
    //  with preventing malicious HTML from getting rendered by itself. This
    //  is more of a legacy from my previous projects, may be removed if you want.
    window.escapeHTML = function(text) {
        return text.replace(/[\"&'\/<>]/g, function (a) {
            return {
                '&': '&amp;',
                '"': '&quot;',
                "'": '&apos;',
                '<': '&lt;',
                '>': '&gt;'
            }[a]
        })
    }
    // Fix the decimal separator for languages that use a comma instead of dot,
    //  for use in preprocessing form inputs and displaying data.
    // This function still relies on some assumptions in order to work and may
    //  need a bit or work.
    window.fixDecimalSeparator = function(value, io) {
        // value is the string value of the number to process
        // io is either 'i' as in input or 'o' as in output
        if (!value) {
            return ''
        }
        if (io === 'i') {
            // Check if it's an American number where a comma precedes a dot
            //  e.g. 12,500.25
            if (value.indexOf('.') > value.indexOf(',')) {
                return value.replace(',', '')
            } else {
                return value.replace(',', '.')
            }
        } else if (io === 'o') {
            return value.toString().replace('.', ',')
        }
    }
    // This will replace the PHP function nl2br in Vue components
    window.nl2br = function(text) {
        return text.replace(/\n/g, '<br />')
    }
    // The following functions may seem a bit redundant, but I have needed them
    //  in previous projects to check or process the inputs, so they can be
    //  useful in the future.
    // A simple function that converts a MySQL datetime into a timestamp.
    window.getTimestamp = function(date) {
        if (date) {
            return new Date(date)
        } else {
            return null
        }
    }
    // Push a new URL to the router, essentially navigating to that page.
    window.goTo = function(url) {
        router.push(url)
    }
    // Notify the user if notifications are allowed
	window.notify = function notify(title, options) {
		if (!('Notification' in window)) {
			return
		} else if (Notification.permission === "granted") {
			var notification = new Notification(title, options)
		} else if (Notification.permission !== 'denied') {
			Notification.requestPermission(function(permission) {
				if (!('permission' in Notification)) {
					Notification.permission = permission
				}
				if (permission === "granted") {
					var notification = new Notification(title, options)
				} else {
					alert(title)
				}
			})
		}
	}
    // This line will also make the injections available in Vue components
    Vue.prototype.$window = window
    Vue.prototype.OC = OC
    // Make translations easier by automatically providing the app name
    let tx = function(text) {
        return window.t('cookbook', text)
    }
    Vue.prototype.t = tx
    // Start the app once document is done loading
    $(document).ready(function () {
        // This is a shameful gimmick but needed at this point since
        //  the recipe view is loaded asyncronously.
        // It has to be kept running, otherwise the app doesn't rerender
        //  for example after editing a recipe and returning to the view.

        const App = Vue.extend(AppMain)
        new App({
            store,
            router,
            i18n,
        }).$mount("#app")
        /*
        const Navi = Vue.extend(AppNavi)
        new Navi({
            store,
            router,
            i18n,
        }).$mount("#app-navigation")
        */
    })
})(OC, window, jQuery)
