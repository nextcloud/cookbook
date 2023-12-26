import RecipeFilter from './RecipeFilter';
import { normalize as normalizeString } from '../utils/string-utils';

/**
 * Implementation for filtering recipes by keywords.
 * @extends RecipeFilter
 */
class RecipeKeywordsFilter extends RecipeFilter {
    /**
     * Constructor for RecipeKeywordsFilter.
     * @param {string|string[]} keywords - The keywords to filter by.
     */
    constructor(keywords) {
        super();
        this.keywords = Array.isArray(keywords)
            ? keywords.map((keyword) => normalizeString(keyword.toLowerCase()))
            : [normalizeString(keywords.toLowerCase())];
    }

    /**
     * Implementation of the filter method for RecipeKeywordsFilter.
     * @param {Object} recipe - The recipe object to be filtered.
     * @returns {boolean} True if the recipe passes the filter, false otherwise.
     */
    filter(recipe) {
        return (
            recipe.keywords &&
            this.keywords.every((keyword) =>
                Array.isArray(recipe.keywords)
                    ? recipe.keywords
                          .map((kw) => normalizeString(kw))
                          .includes(keyword)
                    : normalizeString(recipe.keywords).includes(keyword),
            )
        );
    }
}

export default RecipeKeywordsFilter;
