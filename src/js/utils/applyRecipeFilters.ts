import { Recipe } from 'cookbook/js/Models/schema';
import { RecipeFilter } from 'cookbook/js/RecipeFilters';

/**
 * Function to apply a set of filters on an array of recipes.
 * @param {Object[]} recipes - The array of recipe objects to be filtered.
 * @param {RecipeFilter[]} filters - The array of filters to apply.
 * @returns {Object[]} The filtered array of recipes.
 */
function applyRecipeFilters(
	recipes: Recipe[],
	filters: RecipeFilter[],
): Recipe[] {
	return recipes.filter((recipe) =>
		filters.every((filter) => filter.filter(recipe)),
	);
}

export default applyRecipeFilters;
