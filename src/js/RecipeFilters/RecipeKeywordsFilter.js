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
     * @param {boolean} isCommaSeparated - If the keywords field of the recipe should be handled as a comma-separated list.
     * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
     */
    constructor(
        keywords,
        operator = new AndOperator(),
        isCommaSeparated = false,
    ) {
        super(operator);
        this.keywords = Array.isArray(keywords)
            ? keywords.map((keyword) => normalizeString(keyword))
            : [normalizeString(keywords)];

        this.isCommaSeparated = isCommaSeparated;

        // Ignore empty strings
        this.keywords = this.keywords.filter((k) => k !== '');
    }

    /**
     * Returns a normalized list of keywords attached to the recipe. Takes into account if the isCommaSeparated property
     * is set on this class.
     * @param {Object} recipe - The recipe object whose keywords are to be filtered.
     * @returns {string[]} List of normalized keywords.
     */
    getNormalizedKeywords(recipe) {
        let keywords;

        if (this.isCommaSeparated) {
            if (Array.isArray(recipe.keywords)) {
                keywords = recipe.keywords
                    .map((keyword) => keyword.split(','))
                    .flat();
            } else {
                keywords = recipe.keywords.split(',');
            }
            keywords = keywords.map((keyword) => normalizeString(keyword));
        } else {
            keywords = Array.isArray(recipe.keywords)
                ? recipe.keywords.map((keyword) => normalizeString(keyword))
                : [normalizeString(recipe.keywords)];
        }
        keywords = keywords.filter((k) => k !== '');
        return keywords;
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

        const recipeKeywords = this.getNormalizedKeywords(recipe);

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
