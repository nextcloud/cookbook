/** Nextcloud Cookbook app
 *  I18n module
 *  ----------------------
 *  @license AGPL3 or later
 */
import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.use(VueI18n)

const messages = {
    // Each localization has their own messages as an object
    en: {
        // First the general navigation and control bar translations
        'Home': 'Home',
        'New recipe': 'New recipe',
        'Edit recipe': 'Edit recipe',
        'Print recipe': 'Print recipe',
        'Delete recipe': 'Delete recipe',
        'Confirm delete': 'Are you sure you want to delete this recipe?',
        'Delete failed': 'Failed to delete recipe',
        'Save changes': 'Save changes',
        recipe: {
            view: {
                ingredients: {
                    header: "Ingredients",
                },
                instructions: {
                    header: "Instructions",
                },
                servings: 'Servings',
                source: 'Source',
                timer: {
                    cook: "Cook time",
                    prep: "Preparation time",
                    total: "Total time",
                    up: "Cooking time is up!",
                },
                tools: {
                    header: "Tools",
                },
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
