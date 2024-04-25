import { asArray, asCleanedArray } from 'cookbook/js/helper';
import { Recipe } from 'cookbook/js/Models/schema';
import { normalize as normalizeString } from 'cookbook/js/utils/string-utils';
import {
	AndOperator,
	BinaryOperator,
	OrOperator,
} from 'cookbook/js/LogicOperators';
import { compareArrays } from 'cookbook/js/utils/comparison';
import { simpleRemoveDuplicates } from 'cookbook/js/utils/removeDuplicates';
import FilterType from 'cookbook/js/Enums/FilterType';
import RecipeFilter from './RecipeFilter';

/**
 * Implementation for filtering recipes by keywords.
 * @extends RecipeFilter
 */
class RecipeKeywordsFilter extends RecipeFilter {
	/** List of keywords used for filtering recipe. */
	keywords: string | string[];

	isCommaSeparated: boolean;

	/**
	 * Constructor for RecipeKeywordsFilter.
	 * @param {string|string[]} keywords - The keywords to filter by.
	 * @param {boolean} isCommaSeparated - If the keywords field of the recipe should be handled as a comma-separated list.
	 * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
	 */
	constructor(
		keywords: string | string[],
		operator: BinaryOperator = new AndOperator(),
		isCommaSeparated: boolean = false,
	) {
		super(operator);
		this.type = FilterType.KeywordsFilter;
		this.searchLabel = 'tag';

		this.keywords = simpleRemoveDuplicates(asArray(keywords));
		this.isCommaSeparated = isCommaSeparated;

		// Ignore empty strings
		this.keywords = this.keywords.filter((k) => k !== '');
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
				keywords: string | string[];
			}
		>(<unknown>otherFilter);

		// Type of operator only matters if there is more than one keyword set
		if (
			this.keywords.length > 1 &&
			this.operator.type !== otherFilter.operator.type
		) {
			return false;
		}

		// Create arrays for both filters' keywords parameters
		const otherKeywordsArray = asCleanedArray(comparedFilter.keywords);
		const thisKeywordsArray = asCleanedArray(this.keywords);

		// Compare properties
		return (
			this.isCommaSeparated === comparedFilter.isCommaSeparated &&
			compareArrays(thisKeywordsArray, otherKeywordsArray)
		);
	}

	/**
	 * Returns a normalized list of keywords attached to the recipe. Takes into account if the isCommaSeparated property
	 * is set on this class.
	 * @param {Object} recipe - The recipe object whose keywords are to be filtered.
	 * @returns {string[]} List of normalized keywords.
	 */
	getNormalizedKeywords(recipe: Recipe): string[] {
		let keywords: string[];

		if (this.isCommaSeparated) {
			if (!Array.isArray(recipe.keywords)) {
				throw new Error(
					`Recipe ${recipe.name}: 'keywords' property has unsupported type.`,
				);
			}
			keywords = recipe.keywords
				.map((keyword) => keyword.split(','))
				.flat();

			keywords = keywords.map((keyword) => normalizeString(keyword));
		} else {
			keywords = Array.isArray(recipe.keywords)
				? recipe.keywords.map((keyword) => normalizeString(keyword))
				: [normalizeString(recipe.keywords)];
		}
		keywords = keywords.filter((k) => k !== '');
		return keywords;
	}

	/**
	 * Implementation of the filter method for RecipeKeywordsFilter.
	 * An empty filter list or only empty strings are ignored and evaluate to true.
	 * @param {Recipe} recipe - The recipe object to be filtered.
	 * @returns {boolean} True if the recipe passes the filter, false otherwise.
	 */
	filter(recipe: Recipe): boolean {
		// If no filter is set, return all recipes
		if (this.keywords.length === 0) return true;

		if (!recipe.keywords) {
			return false;
		}

		const recipeKeywords = this.getNormalizedKeywords(recipe);

		let result = this.operator instanceof AndOperator;

		for (const keyword of this.keywords) {
			const keywordMatch = recipeKeywords.includes(
				normalizeString(keyword),
			);
			result = this.operator.apply(result, keywordMatch);

			// If using OrOperator and the result is already true, no need to continue checking
			if (this.operator instanceof OrOperator && result) {
				break;
			}
		}

		return result;
	}

	/**
	 * Determines a string representation of the filter.
	 * @returns {string} String representation.
	 */
	toSearchString(): string {
		return this.operator.generateStringRepresentationForMultipleOperandsWithLabel(
			asArray(this.keywords).map((kw) => `"${kw}"`),
			'tag',
		);
	}
}

export default RecipeKeywordsFilter;
