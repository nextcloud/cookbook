/**
 * Nextcloud Cookbook app
 * Vuex store module
 * ----------------------
 * @license AGPL3 or later
 */
import Vue from 'vue'
import Vuex from 'vuex'
import axios from '@nextcloud/axios'

Vue.use(Vuex)

// We are using the vuex store linking changes within the components to updates in the navigation panel.
export default new Vuex.Store({
    // Vuex store handles value changes through actions and mutations.
    // From the App, you trigger an action, that changes the store
    //  state through a set mutation. You can process the data within
    //  the mutation if you want.
    state: {
        // The left navigation pane (categories, settings, etc.)
        appNavigation:
            {
                // It can be hidden in small browser windows (e.g., on mobile phones)
                visible: true,
                refreshRequired: false
            },
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
        // This is used if when a recipe is reloaded in edit or view
        reloadingRecipe: 0,
        // A recipe save is in progress
        savingRecipe: false,
        // Updating the recipe directory is in progress
        updatingRecipeDirectory: false,
    },

    mutations: {
        setAppNavigationRefreshRequired(s, { b }) {
            s.appNavigation.refreshRequired = b
        },
        setAppNavigationVisible(s, { b }) {
            s.appNavigation.visible = b
        },
        setLoadingRecipe(s, { r }) {
            s.loadingRecipe = r
        },
        setPage(s, { p }) {
            s.page = p
        },
        setRecipe(s, { r }) {
            s.recipe = r
            // Setting recipe also means that loading/reloading the recipe has finished
            s.loadingRecipe = 0
            s.reloadingRecipe = 0
        },
        setReloadingRecipe(s, { r }) {
            s.reloadingRecipe = r
        },
        setSavingRecipe(s, { b }) {
            s.savingRecipe = b
        },
        setUser(s, { u }) {
            s.user = u
        },
        setUpdatingRecipeDirectory(s, { b }) {
            s.updatingRecipeDirectory = b
        }
    },

    actions: {
        setAppNavigationVisible(c, { isVisible }) {
            c.commit('setAppNavigationVisible', { b: isVisible })
        },
        setAppNavigationRefreshRequired(c, { isRequired }) {
            c.commit('setAppNavigationRefreshRequired', {b: isRequired })
        },
        setLoadingRecipe(c, { recipe }) {
            c.commit('setLoadingRecipe', { r: parseInt(recipe) })
        },
        setPage(c, { page }) {
            c.commit('setPage', { p: page })
        },
        setRecipe(c, { recipe }) {
            c.commit('setRecipe', { r: recipe })
        },
        setReloadingRecipe(c, { recipe }) {
            c.commit('setReloadingRecipe', { r: parseInt(recipe) })
        },
        setSavingRecipe(c, { saving }) {
            c.commit('setSavingRecipe', { b: saving })
        },
        setUser(c, { user }) {
            c.commit('setUser', { u: user })
        },
        updateRecipeDirectory(c, { dir }) {
            c.commit('setUpdatingRecipeDirectory', { b: true })
            c.dispatch('setRecipe', { recipe: null })
            const request = axios({
                url: window.baseUrl + '/config',
                method: 'POST',
                data: { 'folder': dir },
            });

            return request.then(() => {
                    c.dispatch('setAppNavigationRefreshRequired', { isRequired: true })
                    c.commit('setUpdatingRecipeDirectory', { b: false })
                })
        },
    }

})
