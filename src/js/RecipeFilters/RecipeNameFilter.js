import RecipeFilter from './RecipeFilter';
import { normalize as normalizeString } from '../utils/string-utils';

/**
 * Implementation for filtering recipes by name.
 * @extends RecipeFilter
 */
class RecipeNameFilter extends RecipeFilter {
    /**
     * Constructor for RecipeNameFilter.
     * @param {string} name - The name to filter by.
     */
    constructor(name) {
        super();
        this.name = name;
    }

    /**
     * Implementation of the filter method for RecipeNameFilter.
     * @param {Object} recipe - The recipe object to be filtered.
     * @returns {boolean} True if the recipe passes the filter, false otherwise.
     */
    filter(recipe) {
        return (
            recipe.name &&
            normalizeString(recipe.name).includes(normalizeString(this.name))
        );
    }
}

export default RecipeNameFilter;
