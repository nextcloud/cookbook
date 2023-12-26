/**
 * Abstract class for a recipe filter.
 * @abstract
 */
class RecipeFilter {
    /**
     * Constructor for the abstract class.
     * @throws {TypeError} Cannot instantiate abstract class.
     */
    constructor() {
        if (new.target === RecipeFilter) {
            throw new TypeError('Cannot instantiate abstract class');
        }
    }

    /**
     * Abstract method to be implemented by subclasses.
     * @param {Object} recipe - The recipe object to be filtered.
     * @throws {Error} Method 'filter' must be implemented by subclasses.
     */
    filter(recipe) {
        throw new Error("Method 'filter' must be implemented by subclasses");
    }
}

// module.exports = RecipeFilter;
export default RecipeFilter;
