import Fuse from 'fuse.js';
import {
	AndOperator,
	BinaryOperator,
	OrOperator,
} from 'cookbook/js/LogicOperators';
import { asArray, asCleanedArray } from 'cookbook/js/helper';
import { Recipe } from 'cookbook/js/Models/schema';
import { normalize as normalizeString } from 'cookbook/js/utils/string-utils';
import { compareArrays } from 'cookbook/js/utils/comparison';
import SearchMode from 'cookbook/js/Enums/SearchMode';
import { simpleRemoveDuplicates } from 'cookbook/js/utils/removeDuplicates';
import FilterType from 'cookbook/js/Enums/FilterType';
import RecipeFilter from './RecipeFilter';

/**
 * Implementation for filtering recipes by names.
 * @extends RecipeFilter
 */
class RecipeNamesFilter extends RecipeFilter {
	/** List of names used for filtering recipe. */
	names: string | string[];

	/** Mode used for filtering, allows to enable fuzzy search. */
	filterMode: SearchMode;

	/** The Fuse.js instance used for filtering. */
	fuse: Fuse<string>;

	/**
	 * Constructor for RecipeNamesFilter.
	 * @param {string|string[]} names - The names to filter by.
	 * @param {BinaryOperator} operator - The binary operator for combining filter conditions.
	 * @param {SearchMode} filterMode - The mode how to filter the recipe names. Exact match; search in substrings of the name; and a fuzzy matching;
	 */
	constructor(
		names: string | string[],
		operator: BinaryOperator = new OrOperator(),
		filterMode: SearchMode = SearchMode.Exact,
	) {
		super(operator);
		this.type = FilterType.NamesFilter;
		this.searchLabel = 'name';
		this.filterMode = filterMode;

		this.names = simpleRemoveDuplicates(asArray(names));

		// Ignore empty strings
		this.names = this.names.filter((n) => n !== '');
	}

	// eslint-disable-next-line class-methods-use-this
	get fuseOptions() {
		return {
			isCaseSensitive: false,
			shouldSort: true,
			minMatchCharLength: 1,
			threshold: 0.45,
			distance: 100,
			// Maybe later
			// useExtendedSearch: false,
		};
	}

	fuseSearch(name: string): boolean {
		return this.fuse.search(name).length > 0;
	}

	/**
	 * Implementation of the filter method for RecipeNamesFilter.
	 * An empty filter list or only empty strings are ignored and evaluate to true.
	 * @param {Object} recipe - The recipe object to be filtered.
	 * @returns {boolean} True if the recipe passes the filter, false otherwise.
	 */
	filter(recipe: Recipe): boolean {
		// If no filter is set, return all recipes
		if (this.names.length === 0) return true;

		if (!recipe.name) {
			return false;
		}

		const recipeNames = asArray(recipe.name).map((name) =>
			normalizeString(name),
		);

		let result = this.operator instanceof AndOperator;

		// Setup fuzzy search
		if (this.filterMode === SearchMode.Fuzzy) {
			this.fuse = new Fuse(recipeNames, this.fuseOptions);
		}

		for (const name of this.names) {
			let nameMatch: boolean;
			const normalizedName = normalizeString(name);

			// If the filter value should be searched in substrings of the recipe names
			if (this.filterMode === SearchMode.MatchSubstring) {
				nameMatch = recipeNames.some((n) => n.includes(normalizedName));
			} else if (this.filterMode === SearchMode.Fuzzy) {
				nameMatch = this.fuseSearch(normalizedName);
			} else {
				nameMatch = recipeNames.includes(normalizedName);
			}
			result = this.operator.apply(result, nameMatch);

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
			asArray(this.names).map((kw) => `"${kw}"`),
			'name',
		);
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
				filterMode: SearchMode;
				names: string | string[];
			}
		>(<unknown>otherFilter);

		// Type of operator only matters if there is more than one name set
		if (
			this.names.length > 1 &&
			this.operator.type !== otherFilter.operator.type
		) {
			return false;
		}

		// Create arrays for both filters' `names` parameters
		const otherNamesArray = asCleanedArray(comparedFilter.names);
		const thisNamesArray = asCleanedArray(this.names);

		// Compare properties
		return (
			this.filterMode === comparedFilter.filterMode &&
			compareArrays(thisNamesArray, otherNamesArray)
		);
	}
}

export default RecipeNamesFilter;
