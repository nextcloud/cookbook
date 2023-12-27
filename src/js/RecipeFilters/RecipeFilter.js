import { BinaryOperator } from '../LogicOperators';

/**
 * Abstract class for a recipe filter.
 * @abstract
 */
class RecipeFilter {
    /**
     * Constructor for the abstract class.
     * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
     * @throws {TypeError} Invalid operator.
     */
    constructor(operator) {
        if (!(operator instanceof BinaryOperator)) {
            throw new TypeError('Invalid operator');
        }
        this.operator = operator;
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
