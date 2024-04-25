import LogicOperatorType from 'cookbook/js/Enums/LogicOperatorType';
import BinaryOperator from './BinaryOperator';

/**
 * Implementation for the OR operator.
 * @extends BinaryOperator
 */
class OrOperator extends BinaryOperator {
	// eslint-disable-next-line class-methods-use-this
	get type(): LogicOperatorType {
		return LogicOperatorType.Or;
	}

	// eslint-disable-next-line class-methods-use-this
	get toString(): string {
		return 'OR';
	}

	/**
	 * Applies the OR operation.
	 * @param {boolean} result - The result accumulated so far.
	 * @param {boolean} current - The current value to apply.
	 * @returns {boolean} The result after applying the OR operation.
	 */
	// eslint-disable-next-line class-methods-use-this
	apply(result, current): boolean {
		return result || current;
	}

	// eslint-disable-next-line class-methods-use-this
	generateStringRepresentationForMultipleOperandsWithLabel(
		items: unknown[],
		label: string,
	): string {
		if (items.length === 0) return '';
		return `${label}:${items.map((item) => `${item}`).join(',')}`;
	}
}

export default OrOperator;
