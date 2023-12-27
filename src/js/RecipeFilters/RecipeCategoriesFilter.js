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
    }

    /**
     * Implementation of the filter method for RecipeCategoriesFilter.
     * An empty filter list or only empty strings are ignored and evaluate to true.
     * @param {Object} recipe - The recipe object to be filtered.
     * @returns {boolean} True if the recipe passes the filter, false otherwise.
     */
    filter(recipe) {
        if (!recipe.recipeCategory) {
            return false;
        }

        // If no filter is set, return all recipes
        if (
            (this.categories.length === 0) |
            this.categories.every((c) => c === '')
        )
            return true;

        const recipeCategories = Array.isArray(recipe.recipeCategory)
            ? recipe.recipeCategory.map((category) => normalizeString(category))
            : [normalizeString(recipe.recipeCategory)];

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
