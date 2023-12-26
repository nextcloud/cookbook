import RecipeFilter from './RecipeFilter';
import { normalize as normalizeString } from '../utils/string-utils';

/**
 * Implementation for filtering recipes by category.
 * @extends RecipeFilter
 */
class RecipeCategoryFilter extends RecipeFilter {
    /**
     * Constructor for RecipeCategoryFilter.
     * @param {string|string[]} category - The category to filter by.
     */
    constructor(category) {
        super();
        this.category = Array.isArray(category)
            ? category.map((cat) => normalizeString(cat))
            : [normalizeString(category)];
    }

    /**
     * Implementation of the filter method for RecipeCategoryFilter.
     * @param {Object} recipe - The recipe object to be filtered.
     * @returns {boolean} True if the recipe passes the filter, false otherwise.
     */
    filter(recipe) {
        return (
            recipe.category &&
            this.category.every((cat) =>
                Array.isArray(recipe.category)
                    ? recipe.category
                          .map((c) => normalizeString(c))
                          .includes(cat)
                    : normalizeString(recipe.category).includes(cat),
            )
        );
    }
}

export default RecipeCategoryFilter;
