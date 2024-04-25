import LogicOperatorType from 'cookbook/js/Enums/LogicOperatorType';
import BinaryOperator from './BinaryOperator';

/**
 * Implementation for the AND operator.
 * @extends BinaryOperator
 */
class AndOperator extends BinaryOperator {
	// eslint-disable-next-line class-methods-use-this
	get type(): LogicOperatorType {
		return LogicOperatorType.And;
	}

	// eslint-disable-next-line class-methods-use-this
	get toString(): string {
		return 'AND';
	}

	/**
	 * Applies the AND operation.
	 * @param {boolean} result - The result accumulated so far.
	 * @param {boolean} current - The current value to apply.
	 * @returns {boolean} The result after applying the AND operation.
	 */
	// eslint-disable-next-line class-methods-use-this
	apply(result, current): boolean {
		return result && current;
	}

	// eslint-disable-next-line class-methods-use-this
	generateStringRepresentationForMultipleOperandsWithLabel(
		items: unknown[],
		label: string,
	): string {
		if (label) {
			return items.map((item) => `${label}:${item}`).join(' ');
		}
		return items.map((item) => `${item}`).join(' ');
	}
}

export default AndOperator;
