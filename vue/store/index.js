/**
 * Nextcloud Cookbook app
 * Vuex store module
 * ----------------------
 * @license AGPL3 or later
 */
import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

// I have found the vuex store especially useful for linking changes
//  within the components to updates in the navigation panel.
// A simple example would be that browsing to a new category
//  (say from 'main courses' to 'desserts') within the app would also
//  make then new category active in the navigation panel.
// I also like to store user data in the store, that is the user ID,
//  name and localization.
export default new Vuex.Store({
    // Vuex store handles value changes through actions and mutations.
    // From the App, you trigger an action, that changes the store
    //  state through a set mutation. You can process the data within
    //  the mutation if you want.
    state: {
        user: null,
        // Page is for keeping track of the page the user is on and
        //  setting the appropriate navigation entry active.
        page: null,
        // We'll save the recipe here, since the data is used by
        //  several independent components
        recipe: null,
        // Loading and saving states to determine which loader icons to show.
        // State of -1 is reserved for recipe and edit views to be set when the
        // User loads the app at one of these locations and has to wait for an
        // asynchronous recipe loading.
        loadingRecipe: 0,
        // A recipe save is in progress
        savingRecipe: false,
    },

    mutations: {
        setLoadingRecipe(s, { r }) {
            s.loadingRecipe = r
        },
        setPage(s, { p }) {
            s.page = p
        },
        setRecipe(s, { r }) {
            s.recipe = r
            // Setting recipe also means that loading the recipe has finished
            s.loadingRecipe = 0
        },
        setSavingRecipe(s, { b }) {
            s.savingRecipe = b
        },
        setUser(s, { u }) {
            s.user = u
        }
    },

    actions: {
        setLoadingRecipe(c, { recipe }) {
            c.commit('setLoadingRecipe', { r: recipe })
        },
        setPage(c, { page }) {
            c.commit('setPage', { p: page })
        },
        setRecipe(c, { recipe }) {
            c.commit('setRecipe', { r: recipe })
        },
        setSavingRecipe(c, { saving }) {
            c.commit('setSavingRecipe', { b: saving })
        },
        setUser(c, { user }) {
            c.commit('setUser', { u: user })
        },
    }

})
