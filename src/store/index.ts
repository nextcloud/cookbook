/**
 * Nextcloud Cookbook app
 * Global store module
 * ----------------------
 * @license AGPL3 or later
 */

import { defineStore } from 'pinia';

import api from '../js/api-interface';

/**
 * Returns setting from stored settings.
 */
function showFiltersInRecipeList(): string {
	return localStorage.getItem('showFiltersInRecipeList') || 'true';
}

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
			this.setCategoryUpdating({ category: oldName });

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
			this.setUpdatingRecipeDirectoryMutation({ b: true });
			this.setRecipe({ recipe: null });
			const request = api.config.directory.update(dir);

			return request.then(() => {
				this.setAppNavigationRefreshRequired({
					isRequired: true,
				});
				this.setUpdatingRecipeDirectoryMutation({ b: false });
			});
		},
		/**
		 * Update existing recipe on the server
		 */
		updateRecipe({ recipe }) {
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

export { useLegacyStore };
