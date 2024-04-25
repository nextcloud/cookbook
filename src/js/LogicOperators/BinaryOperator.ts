import LogicOperatorType from 'cookbook/js/Enums/LogicOperatorType';

/**
 * Abstract class for binary operators.
 * @abstract
 */
class BinaryOperator {
	// eslint-disable-next-line class-methods-use-this
	get type(): LogicOperatorType {
		throw new TypeError('Abstract operator class does not have type');
	}

	// eslint-disable-next-line class-methods-use-this
	get toString(): string {
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
	// eslint-disable-next-line no-unused-vars,@typescript-eslint/no-unused-vars,class-methods-use-this
	apply(result, current): boolean {
		throw new Error("Method 'apply' must be implemented by subclasses");
	}

	// eslint-disable-next-line class-methods-use-this
	generateStringRepresentationForMultipleOperandsWithLabel(
		// eslint-disable-next-line no-unused-vars,@typescript-eslint/no-unused-vars
		items: unknown[],
		// eslint-disable-next-line no-unused-vars,@typescript-eslint/no-unused-vars
		label: string,
	): string {
		throw new Error(
			"Method 'generateStringRepresentationForMultipleWithLabel' must be implemented by subclasses",
		);
	}
}

export default BinaryOperator;
