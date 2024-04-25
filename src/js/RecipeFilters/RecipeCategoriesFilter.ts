import { Recipe } from 'cookbook/js/Models/schema';
import {
	AndOperator,
	BinaryOperator,
	OrOperator,
} from 'cookbook/js/LogicOperators';
import { normalize as normalizeString } from 'cookbook/js/utils/string-utils';
import { asArray, asCleanedArray } from 'cookbook/js/helper';
import { compareArrays } from 'cookbook/js/utils/comparison';
import { simpleRemoveDuplicates } from 'cookbook/js/utils/removeDuplicates';
import FilterType from 'cookbook/js/Enums/FilterType';
import RecipeFilter from './RecipeFilter';

/**
 * Implementation for filtering recipes by categories.
 * @extends RecipeFilter
 */
class RecipeCategoriesFilter extends RecipeFilter {
	/** List of categories used for filtering recipe. */
	categories: string | string[];

	/**
	 * Constructor for RecipeCategoriesFilter.
	 * @param {string|string[]} categories - The categories to filter by.
	 * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
	 */
	constructor(
		categories: string | string[],
		operator: BinaryOperator = new OrOperator(),
	) {
		super(operator);
		this.type = FilterType.CategoriesFilter;
		this.searchLabel = 'cat';

		this.categories = simpleRemoveDuplicates(asArray(categories));

		// Ignore empty strings
		this.categories = this.categories.filter((c) => c !== '');
	}

	/**
	 * Implementation of the filter method for RecipeCategoriesFilter.
	 * An empty filter list or only empty strings are ignored and evaluate to true.
	 * @param {Object} recipe - The recipe object to be filtered.
	 * @returns {boolean} True if the recipe passes the filter, false otherwise.
	 */
	filter(recipe: Recipe): boolean {
		// If no filter is set, return all recipes
		if (this.categories.length === 0) return true;

		if (!recipe.recipeCategory) {
			return false;
		}

		const recipeCategories = Array.isArray(recipe.recipeCategory)
			? recipe.recipeCategory.map((category) => normalizeString(category))
			: [normalizeString(recipe.recipeCategory)];

		let result = this.operator instanceof AndOperator;

		for (const category of this.categories) {
			const categoryMatch = recipeCategories.includes(
				normalizeString(category),
			);
			result = this.operator.apply(result, categoryMatch);

			// If using OrOperator and the result is already true, no need to continue checking
			if (this.operator instanceof OrOperator && result) {
				break;
			}
		}

		return result;
	}

	/**
	 * Compares this filter with another filter for equality based on filtering criteria.
	 * @param {RecipeFilter} otherFilter - The filter to compare with.
	 * @returns {boolean} True if the filters are equivalent, false otherwise.
	 */
	equals(otherFilter: RecipeFilter): boolean {
		// Check that otherFilter has same type
		if (
			otherFilter.type !== this.type ||
			this.searchLabel !== otherFilter.searchLabel
		) {
			return false;
		}

		// Cast to object with these properties
		const comparedFilter = <
			{
				isCommaSeparated: boolean;
				categories: string | string[];
			}
		>(<unknown>otherFilter);

		// Type of operator only matters if there is more than one category set
		if (
			this.categories.length > 1 &&
			this.operator.type !== otherFilter.operator.type
		) {
			return false;
		}

		// Create arrays for both filters' keywords parameters
		const otherCategoriesArray = asCleanedArray(comparedFilter.categories);
		const thisCategoriesArray = asCleanedArray(this.categories);

		// Compare properties
		return compareArrays(thisCategoriesArray, otherCategoriesArray);
	}

	/**
	 * Determines a string representation of the filter.
	 * @returns {string} String representation.
	 */
	toSearchString(): string {
		return this.operator.generateStringRepresentationForMultipleOperandsWithLabel(
			asArray(this.categories).map((kw) => `"${kw}"`),
			'cat',
		);
	}
}

export default RecipeCategoriesFilter;
