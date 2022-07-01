/**
 * Nextcloud Cookbook app
 * Vuex store module
 * ----------------------
 * @license AGPL3 or later
 */
import Vue from "vue"
import Vuex from "vuex"
import api from "cookbook/js/api-interface"

Vue.use(Vuex)

// We are using the vuex store linking changes within the components to updates in the navigation panel.
export default new Vuex.Store({
    // Vuex store handles value changes through actions and mutations.
    // From the App, you trigger an action, that changes the store
    //  state through a set mutation. You can process the data within
    //  the mutation if you want.
    state: {
        // The left navigation pane (categories, settings, etc.)
        appNavigation: {
            // It can be hidden in small browser windows (e.g., on mobile phones)
            visible: true,
            refreshRequired: false,
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
        // Category which is being updated (name)
        categoryUpdating: null,
        localSettings: {
            showTagCloudInRecipeList: true,
        },
    },

    mutations: {
        initializeStore(state) {
            if (localStorage.getItem("showTagCloudInRecipeList")) {
                state.localSettings.showTagCloudInRecipeList = JSON.parse(
                    localStorage.getItem("showTagCloudInRecipeList")
                )
            } else {
                state.localSettings.showTagCloudInRecipeList = true
            }
        },
        setAppNavigationRefreshRequired(state, { b }) {
            state.appNavigation.refreshRequired = b
        },
        setAppNavigationVisible(state, { b }) {
            state.appNavigation.visible = b
        },
        setCategoryUpdating(state, { c }) {
            state.categoryUpdating = c
        },
        setLoadingRecipe(state, { r }) {
            state.loadingRecipe = r
        },
        setPage(state, { p }) {
            state.page = p
        },
        setRecipe(state, { r }) {
            const rec = JSON.parse(JSON.stringify(r))
            if (rec === null) {
                state.recipe = null
                return
            }
            if ("nutrition" in rec && rec.nutrition instanceof Array) {
                rec.nutrition = {}
            }
            state.recipe = rec

            // Setting recipe also means that loading/reloading the recipe has finished
            state.loadingRecipe = 0
            state.reloadingRecipe = 0
        },
        setRecipeCategory(state, { c }) {
            state.recipe.category = c
        },
        setReloadingRecipe(state, { r }) {
            state.reloadingRecipe = r
        },
        setSavingRecipe(state, { b }) {
            state.savingRecipe = b
        },
        setShowTagCloudInRecipeList(state, { b }) {
            localStorage.setItem("showTagCloudInRecipeList", JSON.stringify(b))
            state.localSettings.showTagCloudInRecipeList = b
        },
        setUser(state, { u }) {
            state.user = u
        },
        setUpdatingRecipeDirectory(state, { b }) {
            state.updatingRecipeDirectory = b
        },
    },

    actions: {
        /**
         * Create new recipe on the server
         */
        createRecipe(c, { recipe }) {
            const request = api.recipes.create(recipe)
            return request.then((v) => {
                // Refresh navigation to display changes
                c.dispatch("setAppNavigationRefreshRequired", {
                    isRequired: true,
                })

                return v
            })
        },
        /**
         * Delete recipe on the server
         */
        deleteRecipe(c, { id }) {
            const request = api.recipes.delete(id)
            request.then(() => {
                // Refresh navigation to display changes
                c.dispatch("setAppNavigationRefreshRequired", {
                    isRequired: true,
                })
            })
            return request
        },
        setAppNavigationVisible(c, { isVisible }) {
            c.commit("setAppNavigationVisible", { b: isVisible })
        },
        setAppNavigationRefreshRequired(c, { isRequired }) {
            c.commit("setAppNavigationRefreshRequired", { b: isRequired })
        },
        setLoadingRecipe(c, { recipe }) {
            c.commit("setLoadingRecipe", { r: parseInt(recipe, 10) })
        },
        setPage(c, { page }) {
            c.commit("setPage", { p: page })
        },
        setRecipe(c, { recipe }) {
            c.commit("setRecipe", { r: recipe })
        },
        setReloadingRecipe(c, { recipe }) {
            c.commit("setReloadingRecipe", { r: parseInt(recipe, 10) })
        },
        setSavingRecipe(c, { saving }) {
            c.commit("setSavingRecipe", { b: saving })
        },
        setUser(c, { user }) {
            c.commit("setUser", { u: user })
        },
        setCategoryUpdating(c, { category }) {
            c.commit("setCategoryUpdating", { c: category })
        },
        setShowTagCloudInRecipeList(c, { showTagCloud }) {
            c.commit("setShowTagCloudInRecipeList", { b: showTagCloud })
        },
        updateCategoryName(c, { categoryNames }) {
            const oldName = categoryNames[0]
            const newName = categoryNames[1]
            c.dispatch("setCategoryUpdating", { category: oldName })

            const request = api.categories.update(oldName, newName)

            request
                .then(() => {
                    if (c.state.recipe.recipeCategory === oldName) {
                        c.commit("setRecipeCategory", { c: newName })
                    }
                })
                .catch((e) => {
                    if (e && e instanceof Error) {
                        throw e
                    }
                })
                .then(() => {
                    // finally
                    c.dispatch("setCategoryUpdating", { category: null })
                })

            return request
        },
        updateRecipeDirectory(c, { dir }) {
            c.commit("setUpdatingRecipeDirectory", { b: true })
            c.dispatch("setRecipe", { recipe: null })
            const request = api.config.directory.update(dir)

            request.then(() => {
                c.dispatch("setAppNavigationRefreshRequired", {
                    isRequired: true,
                })
                c.commit("setUpdatingRecipeDirectory", { b: false })
            })
            return request
        },
        /**
         * Update existing recipe on the server
         */
        updateRecipe(c, { recipe }) {
            const request = api.recipes.update(recipe.id, recipe)
            request.then(() => {
                // Refresh navigation to display changes
                c.dispatch("setAppNavigationRefreshRequired", {
                    isRequired: true,
                })
            })
            return request
        },
    },
})
