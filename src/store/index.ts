/**
 * Nextcloud Cookbook app
 * Vuex store module
 * ----------------------
 * @license AGPL3 or later
 */

import { defineStore } from 'pinia';

import Vue from 'vue';
import Vuex from 'vuex';
import api from '../js/api-interface';

Vue.use(Vuex);

/**
 * Returns setting from stored settings.
 */
function showFiltersInRecipeList(): string {
	return localStorage.getItem('showFiltersInRecipeList') || 'true';
}

// We are using the vuex store linking changes within the components to updates in the navigation panel.
const storeVuex = new Vuex.Store({
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
		/**
		 * Data of the current recipe
		 * @type {Object|null}
		 */
		recipe: <any>null,
		// Filter applied to a list of recipes
		recipeFilters: '',
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
			showFiltersInRecipeList: true,
		},
		config: null,
	},

	mutations: {
		setConfig(state, { config }) {
			state.config = config;
		},
		initializeStore(state) {
			if (localStorage.getItem('showFiltersInRecipeList')) {
				state.localSettings.showFiltersInRecipeList = JSON.parse(
					showFiltersInRecipeList(),
				);
			} else {
				state.localSettings.showFiltersInRecipeList = true;
			}
		},
		setAppNavigationRefreshRequired(state, { b }) {
			state.appNavigation.refreshRequired = b;
		},
		setAppNavigationVisible(state, { b }) {
			state.appNavigation.visible = b;
		},
		setCategoryUpdating(state, { c }) {
			state.categoryUpdating = c;
		},
		setLoadingRecipe(state, { r }) {
			state.loadingRecipe = r;
		},
		setPage(state, { p }) {
			state.page = p;
		},
		setRecipe(state, { r }) {
			const rec = JSON.parse(JSON.stringify(r));
			if (rec === null) {
				state.recipe = null;
				return;
			}
			if ('nutrition' in rec && rec.nutrition instanceof Array) {
				rec.nutrition = {};
			}
			state.recipe = rec;

			// Setting recipe also means that loading/reloading the recipe has finished
			state.loadingRecipe = 0;
			state.reloadingRecipe = 0;
		},
		setRecipeCategory(state, { c }) {
			if (state.recipe !== null) {
				state.recipe.category = c;
			}
		},
		setRecipeFilters(state, { f }) {
			state.recipeFilters = f;
		},
		setReloadingRecipe(state, { r }) {
			state.reloadingRecipe = r;
		},
		setSavingRecipe(state, { b }) {
			state.savingRecipe = b;
		},
		setShowFiltersInRecipeList(state, { b }) {
			localStorage.setItem('showFiltersInRecipeList', JSON.stringify(b));
			state.localSettings.showFiltersInRecipeList = b;
		},
		setUser(state, { u }) {
			state.user = u;
		},
		setUpdatingRecipeDirectory(state, { b }) {
			state.updatingRecipeDirectory = b;
		},
	},

	actions: {
		/**
		 * Read/Update the user settings from the backend
		 */
		async refreshConfig(c) {
			const config = (await api.config.get()).data;
			c.commit('setConfig', { config });
		},

		/*
		 * Clears all filters currently applied for listing recipes.
		 */
		clearRecipeFilters(c) {
			c.commit('setRecipeFilters', { f: '' });
		},

		/**
		 * Create new recipe on the server
		 */
		createRecipe(c, { recipe }) {
			const request = api.recipes.create(recipe);
			return request.then((v) => {
				// Refresh navigation to display changes
				c.dispatch('setAppNavigationRefreshRequired', {
					isRequired: true,
				});

				return v;
			});
		},
		/**
		 * Delete recipe on the server
		 */
		deleteRecipe(c, { id }) {
			const request = api.recipes.delete(id);
			request.then(() => {
				// Refresh navigation to display changes
				c.dispatch('setAppNavigationRefreshRequired', {
					isRequired: true,
				});
			});
			return request;
		},
		setAppNavigationVisible(c, { isVisible }) {
			c.commit('setAppNavigationVisible', { b: isVisible });
		},
		setAppNavigationRefreshRequired(c, { isRequired }) {
			c.commit('setAppNavigationRefreshRequired', { b: isRequired });
		},
		setLoadingRecipe(c, { recipe }) {
			c.commit('setLoadingRecipe', { r: parseInt(recipe, 10) });
		},
		setPage(c, { page }) {
			c.commit('setPage', { p: page });
		},
		setRecipe(c, { recipe }) {
			c.commit('setRecipe', { r: recipe });
		},
		setRecipeFilters(c, filters) {
			c.commit('setRecipeFilters', { f: filters });
		},
		setReloadingRecipe(c, { recipe }) {
			c.commit('setReloadingRecipe', { r: parseInt(recipe, 10) });
		},
		setSavingRecipe(c, { saving }) {
			c.commit('setSavingRecipe', { b: saving });
		},
		setUser(c, { user }) {
			c.commit('setUser', { u: user });
		},
		setCategoryUpdating(c, { category }) {
			c.commit('setCategoryUpdating', { c: category });
		},
		setShowFiltersInRecipeList(c, { showFilters }) {
			c.commit('setShowFiltersInRecipeList', { b: showFilters });
		},
		updateCategoryName(c, { categoryNames }) {
			const oldName = categoryNames[0];
			const newName = categoryNames[1];
			c.dispatch('setCategoryUpdating', { category: oldName });

			const request = api.categories.update(oldName, newName);

			request
				.then(() => {
					if (
						c.state.recipe &&
						c.state.recipe.recipeCategory === oldName
					) {
						c.commit('setRecipeCategory', { c: newName });
					}
				})
				.catch((e) => {
					if (e && e instanceof Error) {
						throw e;
					}
				})
				.then(() => {
					// finally
					c.dispatch('setCategoryUpdating', { category: null });
				});

			return request;
		},
		updateRecipeDirectory(c, { dir }) {
			c.commit('setUpdatingRecipeDirectory', { b: true });
			c.dispatch('setRecipe', { recipe: null });
			const request = api.config.directory.update(dir);

			return request.then(() => {
				c.dispatch('setAppNavigationRefreshRequired', {
					isRequired: true,
				});
				c.commit('setUpdatingRecipeDirectory', { b: false });
			});
		},
		/**
		 * Update existing recipe on the server
		 */
		updateRecipe(c, { recipe }) {
			const request = api.recipes.update(recipe.id, recipe);
			request.then(() => {
				// Refresh navigation to display changes
				c.dispatch('setAppNavigationRefreshRequired', {
					isRequired: true,
				});
			});
			return request;
		},
	},
});

const useLegacyStore = defineStore('legacyStore', {
	state: () =>
		// Vuex store handles value changes through actions and mutations.
		// From the App, you trigger an action, that changes the store
		//  state through a set mutation. You can process the data within
		//  the mutation if you want.
		({
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
			/**
			 * Data of the current recipe
			 * @type {Object|null}
			 */
			recipe: <any>null,
			// Filter applied to a list of recipes
			recipeFilters: '',
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
				showFiltersInRecipeList: true,
			},
			config: null,
		}),
	actions: {
		// ****************************
		// Mutations migrated to actions
		// ****************************
		setConfig({ config }) {
			this.config = config;
		},
		initializeStore() {
			if (localStorage.getItem('showFiltersInRecipeList')) {
				this.localSettings.showFiltersInRecipeList = JSON.parse(
					showFiltersInRecipeList(),
				);
			} else {
				this.localSettings.showFiltersInRecipeList = true;
			}
		},
		setAppNavigationRefreshRequiredMutation({ b }) {
			this.appNavigation.refreshRequired = b;
		},
		setAppNavigationVisibleMutation({ b }) {
			this.appNavigation.visible = b;
		},
		setCategoryUpdatingMutation({ c }) {
			this.categoryUpdating = c;
		},
		setLoadingRecipeMutation({ r }) {
			this.loadingRecipe = r;
		},
		setPageMutation({ p }) {
			this.page = p;
		},
		setRecipeMutation({ r }) {
			const rec = JSON.parse(JSON.stringify(r));
			if (rec === null) {
				this.recipe = null;
				return;
			}
			if ('nutrition' in rec && rec.nutrition instanceof Array) {
				rec.nutrition = {};
			}
			this.recipe = rec;

			// Setting recipe also means that loading/reloading the recipe has finished
			this.loadingRecipe = 0;
			this.reloadingRecipe = 0;
		},
		setRecipeCategoryMutation({ c }) {
			if (this.recipe !== null) {
				this.recipe.category = c;
			}
		},
		setRecipeFiltersMutation({ f }) {
			this.recipeFilters = f;
		},
		setReloadingRecipeMutation({ r }) {
			this.reloadingRecipe = r;
		},
		setSavingRecipeMutation({ b }) {
			this.savingRecipe = b;
		},
		setShowFiltersInRecipeListMutation({ b }) {
			localStorage.setItem('showFiltersInRecipeList', JSON.stringify(b));
			this.localSettings.showFiltersInRecipeList = b;
		},
		setUserMutation({ u }) {
			this.user = u;
		},
		setUpdatingRecipeDirectoryMutation({ b }) {
			this.updatingRecipeDirectory = b;
		},

		// ****************************
		// Actions migrated
		// ****************************
		/**
		 * Read/Update the user settings from the backend
		 */
		async refreshConfig() {
			const config = (await api.config.get()).data;
			this.setConfig({ config });
		},

		/*
		 * Clears all filters currently applied for listing recipes.
		 */
		clearRecipeFilters() {
			this.setRecipeFiltersMutation({ f: '' });
		},

		/**
		 * Create new recipe on the server
		 */
		createRecipe({ recipe }) {
			const request = api.recipes.create(recipe);
			return request.then((v) => {
				// Refresh navigation to display changes
				this.setAppNavigationRefreshRequired({
					isRequired: true,
				});

				return v;
			});
		},
		/**
		 * Delete recipe on the server
		 */
		deleteRecipe({ id }) {
			const request = api.recipes.delete(id);
			request.then(() => {
				// Refresh navigation to display changes
				this.setAppNavigationRefreshRequired({
					isRequired: true,
				});
			});
			return request;
		},
		setAppNavigationVisible({ isVisible }) {
			this.setAppNavigationVisibleMutation({ b: isVisible });
		},
		setAppNavigationRefreshRequired({ isRequired }) {
			this.setAppNavigationRefreshRequiredMutation({ b: isRequired });
		},
		setLoadingRecipe({ recipe }) {
			this.setLoadingRecipeMutation({ r: parseInt(recipe, 10) });
		},
		setPage({ page }) {
			this.setPageMutation({ p: page });
		},
		setRecipe({ recipe }) {
			this.setRecipeMutation({ r: recipe });
		},
		setRecipeFilters(filters) {
			this.setRecipeFiltersMutation({ f: filters });
		},
		setReloadingRecipe({ recipe }) {
			this.setReloadingRecipeMutation({ r: parseInt(recipe, 10) });
		},
		setSavingRecipe({ saving }) {
			this.setSavingRecipeMutation({ b: saving });
		},
		setUser({ user }) {
			this.setUserMutation({ u: user });
		},
		setCategoryUpdating({ category }) {
			this.setCategoryUpdatingMutation({ c: category });
		},
		setShowFiltersInRecipeList({ showFilters }) {
			this.setShowFiltersInRecipeListMutation({ b: showFilters });
		},
		updateCategoryName({ categoryNames }) {
			const oldName = categoryNames[0];
			const newName = categoryNames[1];
			c.dispatch('setCategoryUpdating', { category: oldName });

			const request = api.categories.update(oldName, newName);

			request
				.then(() => {
					if (this.recipe && this.recipe.recipeCategory === oldName) {
						this.setRecipeCategoryMutation({ c: newName });
					}
				})
				.catch((e) => {
					if (e && e instanceof Error) {
						throw e;
					}
				})
				.then(() => {
					// finally
					this.setCategoryUpdating({ category: null });
				});

			return request;
		},
		updateRecipeDirectory({ dir }) {
			this.setUpdatingRecipeDirectoryMigration({ b: true });
			this.setRecipe({ recipe: null });
			const request = api.config.directory.update(dir);

			return request.then(() => {
				this.setAppNavigationRefreshRequired({
					isRequired: true,
				});
				this.setUpdatingRecipeDirectoryMigration({ b: false });
			});
		},
		/**
		 * Update existing recipe on the server
		 */
		updateRecipe(c, { recipe }) {
			const request = api.recipes.update(recipe.id, recipe);
			request.then(() => {
				// Refresh navigation to display changes
				this.setAppNavigationRefreshRequired({
					isRequired: true,
				});
			});
			return request;
		},
	},
});

// eslint-disable-next-line import/prefer-default-export
export const useStore = () => storeVuex;

export { useLegacyStore };
