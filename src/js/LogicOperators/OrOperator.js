import BinaryOperator from './BinaryOperator';

/**
 * Implementation for the OR operator.
 * @extends BinaryOperator
 */
class OrOperator extends BinaryOperator {
    // eslint-disable-next-line class-methods-use-this
    get toString() {
        return 'OR';
    }

    /**
     * Applies the OR operation.
     * @param {boolean} result - The result accumulated so far.
     * @param {boolean} current - The current value to apply.
     * @returns {boolean} The result after applying the OR operation.
     */
    // eslint-disable-next-line class-methods-use-this
    apply(result, current) {
        return result || current;
    }
}

export default OrOperator;
