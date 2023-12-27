import RecipeFilter from './RecipeFilter';
import { AndOperator, OrOperator } from '../LogicOperators';
import { normalize as normalizeString } from '../utils/string-utils';

/**
 * Implementation for filtering recipes by keywords.
 * @extends RecipeFilter
 */
class RecipeKeywordsFilter extends RecipeFilter {
    /**
     * Constructor for RecipeKeywordsFilter.
     * @param {string|string[]} keywords - The keywords to filter by.
     * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
     */
    constructor(keywords, operator = new AndOperator()) {
        super(operator);
        this.keywords = Array.isArray(keywords)
            ? keywords.map((keyword) => normalizeString(keyword))
            : [normalizeString(keywords)];

        // Ignore empty strings
        this.keywords = this.keywords.filter((k) => k !== '');
    }

    /**
     * Implementation of the filter method for RecipeKeywordsFilter.
     * An empty filter list or only empty strings are ignored and evaluate to true.
     * @param {Object} recipe - The recipe object to be filtered.
     * @returns {boolean} True if the recipe passes the filter, false otherwise.
     */
    filter(recipe) {
        // If no filter is set, return all recipes
        if (this.keywords.length === 0) return true;

        if (!recipe.keywords) {
            return false;
        }

        const recipeKeywords = Array.isArray(recipe.keywords)
            ? recipe.keywords.map((keyword) => normalizeString(keyword))
            : [normalizeString(recipe.keywords)];

        let result = this.operator instanceof AndOperator;

        for (const keyword of this.keywords) {
            const keywordMatch = recipeKeywords.includes(keyword);
            result = this.operator.apply(result, keywordMatch);

            // If using OrOperator and the result is already true, no need to continue checking
            if (this.operator instanceof OrOperator && result) {
                break;
            }
        }

        return result;
    }
}

export default RecipeKeywordsFilter;
