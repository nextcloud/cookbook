import BinaryOperator from './BinaryOperator';

/**
 * Implementation for the AND operator.
 * @extends BinaryOperator
 */
class AndOperator extends BinaryOperator {
    // eslint-disable-next-line class-methods-use-this
    get toString() {
        return 'AND';
    }

    /**
     * Applies the AND operation.
     * @param {boolean} result - The result accumulated so far.
     * @param {boolean} current - The current value to apply.
     * @returns {boolean} The result after applying the AND operation.
     */
    // eslint-disable-next-line class-methods-use-this
    apply(result, current) {
        return result && current;
    }
}

export default AndOperator;
