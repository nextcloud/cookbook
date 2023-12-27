import RecipeFilter from './RecipeFilter';
import { AndOperator, OrOperator } from '../LogicOperators';
import { normalize as normalizeString } from '../utils/string-utils';

/**
 * Implementation for filtering recipes by categories.
 * @extends RecipeFilter
 */
class RecipeCategoriesFilter extends RecipeFilter {
    /**
     * Constructor for RecipeCategoriesFilter.
     * @param {string|string[]} categories - The categories to filter by.
     * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
     */
    constructor(categories, operator = new AndOperator()) {
        super(operator);
        this.categories = Array.isArray(categories)
            ? categories.map((category) => normalizeString(category))
            : [normalizeString(categories)];

        // Ignore empty strings
        this.categories = this.categories.filter((c) => c !== '');
    }

    /**
     * Implementation of the filter method for RecipeCategoriesFilter.
     * An empty filter list or only empty strings are ignored and evaluate to true.
     * @param {Object} recipe - The recipe object to be filtered.
     * @returns {boolean} True if the recipe passes the filter, false otherwise.
     */
    filter(recipe) {
        // If no filter is set, return all recipes
        if (this.categories.length === 0) return true;

        if (!recipe.category) {
            return false;
        }

        const recipeCategories = Array.isArray(recipe.category)
            ? recipe.category.map((category) => normalizeString(category))
            : [normalizeString(recipe.category)];

        let result = this.operator instanceof AndOperator;

        for (const category of this.categories) {
            const categoryMatch = recipeCategories.includes(category);
            result = this.operator.apply(result, categoryMatch);

            // If using OrOperator and the result is already true, no need to continue checking
            if (this.operator instanceof OrOperator && result) {
                break;
            }
        }

        return result;
    }
}

export default RecipeCategoriesFilter;
