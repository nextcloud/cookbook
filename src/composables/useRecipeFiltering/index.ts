import Vue, { computed, Ref, ref } from 'vue';
import applyRecipeFilters from 'cookbook/js/utils/applyRecipeFilters';
import { RecipeFilter } from 'cookbook/js/RecipeFilters';
import { Recipe } from 'cookbook/js/Models/schema';

export default function useRecipeFiltering(
	props,
	recipeFilters: Ref<RecipeFilter[]>,
) {
	/**
	 * Defines the sorting order of the list of recipes.
	 */
	const orderBy: Ref<{
		label: string;
		iconUp: boolean;
		recipeProperty: string;
		order: string;
	}> = ref({
		label: Vue.prototype.t('cookbook', 'Name'),
		iconUp: true,
		recipeProperty: 'name',
		order: 'ascending',
	});

	// ===================
	// Methods
	// ===================
	/* Sort recipes according to the property of the recipe ascending or
	 * descending
	 */
	const sortRecipes = (
		recipes: Recipe[],
		recipeProperty: string,
		order: string,
	) => {
		const rec = JSON.parse(JSON.stringify(recipes));
		return rec.sort((r1: Recipe, r2: Recipe) => {
			if (order !== 'ascending' && order !== 'descending') return 0;
			if (order === 'ascending') {
				if (
					recipeProperty === 'dateCreated' ||
					recipeProperty === 'dateModified'
				) {
					if (
						typeof r1[recipeProperty] === 'string' &&
						typeof r2[recipeProperty] === 'string'
					) {
						// https://stackoverflow.com/a/60688789
						return (
							new Date(r1[<string>recipeProperty]).valueOf() -
							new Date(r2[<string>recipeProperty]).valueOf()
						);
					}
				}
				if (recipeProperty === 'name') {
					return r1[recipeProperty].localeCompare(r2[recipeProperty]);
				}
				if (!Number.isNaN(r1[recipeProperty] - r2[recipeProperty])) {
					return r1[recipeProperty] - r2[recipeProperty];
				}
				return 0;
			}

			if (
				recipeProperty === 'dateCreated' ||
				recipeProperty === 'dateModified'
			) {
				if (
					typeof r1[recipeProperty] === 'string' &&
					typeof r2[recipeProperty] === 'string'
				) {
					// https://stackoverflow.com/a/60688789
					return (
						new Date(r2[<string>recipeProperty]).valueOf() -
						new Date(r1[<string>recipeProperty]).valueOf()
					);
				}
			}
			if (recipeProperty === 'name') {
				return r2[recipeProperty].localeCompare(r1[recipeProperty]);
			}
			if (!Number.isNaN(r2[recipeProperty] - r1[recipeProperty])) {
				return r2[recipeProperty] - r1[recipeProperty];
			}
			return 0;
		});
	};

	// ===================
	// Computed properties
	// ===================

	/**
	 * An array of the filtered recipes, with all filters applied.
	 */
	const filteredRecipes = computed(() =>
		applyRecipeFilters(props.recipes, recipeFilters.value),
	);

	// Recipes ordered ascending by name
	const recipesNameAsc = computed(() =>
		sortRecipes(props.recipes, 'name', 'ascending'),
	);

	// Recipes ordered descending by name
	const recipesNameDesc = computed(() =>
		sortRecipes(props.recipes, 'name', 'descending'),
	);

	// Recipes ordered ascending by creation date
	const recipesDateCreatedAsc = computed(() =>
		sortRecipes(props.recipes, 'dateCreated', 'ascending'),
	);

	// Recipes ordered descending by creation date
	const recipesDateCreatedDesc = computed(() =>
		sortRecipes(props.recipes, 'dateCreated', 'descending'),
	);

	// Recipes ordered ascending by modification date
	const recipesDateModifiedAsc = computed(() =>
		sortRecipes(props.recipes, 'dateModified', 'ascending'),
	);

	// Recipes ordered descending by modification date
	const recipesDateModifiedDesc = computed(() =>
		sortRecipes(props.recipes, 'dateModified', 'descending'),
	);

	/**
	 *  A filtered and sorted array of recipe objects with the `show` property determining if the recipe should be
	 *  shown or hidden from the UI.
	 */
	const recipeObjects = computed(() => {
		function makeObject(rec: Recipe): { recipe: Recipe; show: boolean } {
			return {
				recipe: rec,
				show: filteredRecipes.value
					.map((r: Recipe) => r.identifier)
					.includes(rec.identifier),
			};
		}

		if (
			orderBy.value === null ||
			orderBy.value === undefined ||
			(orderBy.value.order !== 'ascending' &&
				orderBy.value.order !== 'descending')
		) {
			return props.recipes.map(makeObject);
		}
		if (orderBy.value.recipeProperty === 'dateCreated') {
			if (orderBy.value.order === 'ascending') {
				return recipesDateCreatedAsc.value.map(makeObject);
			}
			return recipesDateCreatedDesc.value.map(makeObject);
		}
		if (orderBy.value.recipeProperty === 'dateModified') {
			if (orderBy.value.order === 'ascending') {
				return recipesDateModifiedAsc.value.map(makeObject);
			}
			return recipesDateModifiedDesc.value.map(makeObject);
		}
		if (orderBy.value.recipeProperty === 'name') {
			if (orderBy.value.order === 'ascending') {
				return recipesNameAsc.value.map(makeObject);
			}
			return recipesNameDesc.value.map(makeObject);
		}
		return props.recipes.map(makeObject);
	});

	return {
		orderBy,
		recipeObjects,
	};
}
