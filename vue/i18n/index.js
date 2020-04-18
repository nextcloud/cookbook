/** Nextcloud Cookbook app
 *  I18n module
 *  ----------------------
 *  @license AGPL3 or later
 */
import Vue from 'vue'
import VueI18n from 'vue-i18n'

const messages = {
    // Each localization has their own messages as an object
    en: {
        recipe: {
            view: {

            },
        },
    },
}
// Datetime localizations will be described here
const dateTimeFormats = {

    'en-US': {

        short: {
            year: 'numeric', month: 'numeric', day: 'numeric'
        },
        long: {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: 'numeric', minute: 'numeric'
        },

    },
}
export default new VueI18n({
    locale: 'en',
    fallbackLocale: 'en',
    messages: messages,
    dateTimeFormats: dateTimeFormats
})
