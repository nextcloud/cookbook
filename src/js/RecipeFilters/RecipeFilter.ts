import { BinaryOperator } from 'cookbook/js/LogicOperators';
import { Recipe } from 'cookbook/js/Models/schema';
import FilterType from 'cookbook/js/Enums/FilterType';

/**
 * Abstract class for a recipe filter.
 * @abstract
 */
class RecipeFilter {
	/** Type of the RecipeFilter. Can be used to, e.g., compare filters. */
	type: FilterType;

	operator: BinaryOperator;

	/** Label to be used when filter is converted to string representation */
	searchLabel: string;

	/**
	 * Constructor for the abstract class.
	 * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
	 * @throws {TypeError} Invalid operator.
	 */
	constructor(operator: BinaryOperator) {
		if (!(operator instanceof BinaryOperator)) {
			throw new TypeError('Invalid operator');
		}
		this.operator = operator;
	}

	/**
	 * Abstract method to be overridden by subclasses.
	 * Compares this filter with another filter for equality based on filtering criteria.
	 * @param {RecipeFilter} otherFilter - The filter to compare with.
	 * @returns {boolean} True if the filters are equivalent, false otherwise.
	 */
	equals(otherFilter: RecipeFilter): boolean {
		// This is a simplistic implementation. You'll need to customize this
		// based on the properties of your filters.
		return (
			this.type === otherFilter.type &&
			this.constructor === otherFilter.constructor &&
			this.operator === otherFilter.operator
		);
	}

	/**
	 * Abstract method to be implemented by subclasses.
	 * @param {Recipe} recipe - The recipe object to be filtered.
	 * @throws {Error} Method 'filter' must be implemented by subclasses.
	 */
	// eslint-disable-next-line class-methods-use-this,no-unused-vars,@typescript-eslint/no-unused-vars
	filter(recipe: Recipe) {
		throw new Error("Method 'filter' must be implemented by subclasses");
	}

	/**
	 * Abstract method to be implemented by subclasses.
	 * Determines a string representation of the filter.
	 * @throws {Error} Method 'filter' must be implemented by subclasses.
	 * @returns {string} String representation.
	 */
	// eslint-disable-next-line class-methods-use-this,no-unused-vars,@typescript-eslint/no-unused-vars
	toSearchString(): string {
		throw new Error(
			"Method 'toSearchString' must be implemented by subclasses",
		);
	}
}

// module.exports = RecipeFilter;
export default RecipeFilter;
