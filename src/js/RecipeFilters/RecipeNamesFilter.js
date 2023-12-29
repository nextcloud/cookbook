import RecipeFilter from './RecipeFilter';
import { AndOperator, OrOperator } from '../LogicOperators';
import { normalize as normalizeString } from '../utils/string-utils';

/**
 * Implementation for filtering recipes by names.
 * @extends RecipeFilter
 */
class RecipeNamesFilter extends RecipeFilter {
    /**
     * Constructor for RecipeNamesFilter.
     * @param {string|string[]} names - The names to filter by.
     * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
     * @param {boolean} filterSubstring - If true, filter searches in substrings of the name for matches.
     */
    constructor(names, operator = new OrOperator(), filterSubstring = true) {
        super(operator);
        this.names = Array.isArray(names)
            ? names.map((name) => normalizeString(name))
            : [normalizeString(names)];
        this.filterSubstring = filterSubstring;
        // Ignore empty strings
        this.names = this.names.filter((n) => n !== '');
    }

    /**
     * Implementation of the filter method for RecipeNamesFilter.
     * An empty filter list or only empty strings are ignored and evaluate to true.
     * @param {Object} recipe - The recipe object to be filtered.
     * @returns {boolean} True if the recipe passes the filter, false otherwise.
     */
    filter(recipe) {
        // If no filter is set, return all recipes
        if (this.names.length === 0) return true;

        if (!recipe.name) {
            return false;
        }

        const recipeNames = Array.isArray(recipe.name)
            ? recipe.name.map((name) => normalizeString(name))
            : [normalizeString(recipe.name)];

        let result = this.operator instanceof AndOperator;

        for (const name of this.names) {
            let nameMatch;

            // If the filter value should be searched in substrings of the recipe names
            if (this.filterSubstring) {
                nameMatch = recipeNames.some((n) => n.includes(name));
            } else {
                nameMatch = recipeNames.includes(name);
            }
            result = this.operator.apply(result, nameMatch);

            // If using OrOperator and the result is already true, no need to continue checking
            if (this.operator instanceof OrOperator && result) {
                break;
            }
        }

        return result;
    }
}

export default RecipeNamesFilter;
