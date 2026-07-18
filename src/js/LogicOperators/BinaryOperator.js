/**
 * Abstract class for binary operators.
 * @abstract
 */
class BinaryOperator {
    get toString() {
        return 'Abstract operation base class';
    }

    /**
     * Constructor for the abstract class.
     * @throws {TypeError} Cannot instantiate abstract class.
     */
    constructor() {
        if (new.target === BinaryOperator) {
            throw new TypeError('Cannot instantiate abstract class');
        }
    }

    /**
     * Abstract method to be implemented by subclasses.
     * @param {boolean} result - The result accumulated so far.
     * @param {boolean} current - The current value to apply.
     * @throws {Error} Method 'apply' must be implemented by subclasses.
     */

    apply(result, current) {
        throw new Error("Method 'apply' must be implemented by subclasses");
    }
}

export default BinaryOperator;
