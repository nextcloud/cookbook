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
import RecipeView from './components/RecipeView'

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
    // This line will also make the injections available in Vue components
    Vue.prototype.$window = window

    // Start the app once document is done loading
    $(document).ready(function () {
        const App = Vue.extend(RecipeView)
        new App({
            store,
            router,
            i18n
        }).$mount("#app-recipe-view")
    })
})(OC, window, jQuery)
