import BinaryOperator from './BinaryOperator';

/**
 * Implementation for the AND operator.
 * @extends BinaryOperator
 */
class AndOperator extends BinaryOperator {
    /**
     * Applies the AND operation.
     * @param {boolean} result - The result accumulated so far.
     * @param {boolean} current - The current value to apply.
     * @returns {boolean} The result after applying the AND operation.
     */
    apply(result, current) {
        return result && current;
    }
}

export default AndOperator;
