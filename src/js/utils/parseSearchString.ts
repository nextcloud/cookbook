import RecipeFilter from 'cookbook/js/RecipeFilters/RecipeFilter';
import { AndOperator, OrOperator } from 'cookbook/js/LogicOperators';
import {
	RecipeCategoriesFilter,
	RecipeKeywordsFilter,
	RecipeNamesFilter,
	RecipeSearchFilter,
} from 'cookbook/js/RecipeFilters';
import SearchMode from 'cookbook/js/Enums/SearchMode';
import { removeDuplicatesInNestedStringArray } from 'cookbook/js/utils/removeDuplicates';

/** Extracts from a string pairs of a label and one or multiple values that are separated by a colon. The list of values
 * must be comma-separated and values that contain spaces need to be quoted.
 * Example: `tag:chocolate,"very tasty" cat:"dessert"` -> `[{label: "tag", value:"chocolate,"very tasty"}, {label:"cat",value:"dessert" }]`
 * @param searchString The string to split into key-value pairs.
 * @returns {Array<{label: string, values: string[]}>} An array of objects containing label and values.
 */
function extractLabelValuePairs(
	searchString: string,
): Array<{ label: string; value: string }> {
	// Extracts from `tag:"dessert",tasty,"green sauce"` two groups `tag` and `"dessert",tasty,"green sauce"`
	const regex = /(\w+):((?:(?:"[\w\s]+"|\w+),)*(?:"[\w\s]+"|\w+))/g;

	const pairs: { label: string; value: string }[] = [];

	let match: RegExpExecArray | null = regex.exec(searchString);
	while (match !== null) {
		pairs.push({ label: match[1], value: match[2] });
		// iterate
		match = regex.exec(searchString);
	}

	return pairs;
}

/**
 * Processes value behind a label in the search string and extracts the comma-separated values.
 * @param value The string of the value.
 */
function processValue(value: string): string[] {
	// Split by commas for OR logic and remove quotes if present
	// return value.split(',').map((v) => v.replace(/^"|"$/g, '').trim());
	return value.split(',');
}

/**
 * Creates a filter object based on the label and values.
 * @param {string} label - The label of the filter.
 * @param {string[]} values - The values associated with the filter.
 * @param {AndOperator|OrOperator} operator - The logical operator for the filter.
 * @returns {RecipeFilter} A filter object.
 */
function createFilter(
	label: string,
	values: string[],
	operator: AndOperator | OrOperator,
): RecipeFilter {
	switch (label) {
		case 'tag':
			return new RecipeKeywordsFilter(values, operator);
		case 'cat':
			return new RecipeCategoriesFilter(values, operator);
		case 'name':
			return new RecipeNamesFilter(
				values,
				operator,
				SearchMode.MatchSubstring,
			);
		default:
			throw new Error(`Unknown filter type: ${label}`);
	}
}

/**
 * Trim and remove quotes if present.
 * @param {string} term - Search term
 */
function normalizeFilterTerm(term: string): string {
	return term.replace(/^"|"$/g, '').trim();
}

/**
 * Creates filter objects from label/value pairs.
 * Combines multiple single-item filters of the same type into a single filter with an AndOperator.
 * @param {Array<{label: string, value: string}>} labelValuePairs - An array of label/value pairs.
 * @param {string} originalSearchString - The original search string.
 * @returns {filters: {Array<RecipeFilter>, remainingSearchString: string}} An object with an array of filter objects
 * and the remaining search string without those filters.
 */
function createFilters(
	labelValuePairs: Array<{ label: string; value: string }>,
	originalSearchString: string,
): { filters: RecipeFilter[]; remainingSearchString: string } {
	const filterMap: Map<string, string[][]> = new Map();

	// Copy string to be modified
	let updatedSearchString = ` ${originalSearchString}`.slice(1);

	// Group values by their label
	labelValuePairs.forEach(({ label, value }) => {
		const values = processValue(value);

		if (!filterMap.has(label)) {
			filterMap.set(label, []);
		}
		filterMap.get(label)?.push(values);
	});

	// Cleanup duplicates
	filterMap.forEach((values, label) => {
		filterMap[label] = removeDuplicatesInNestedStringArray(values);
	});

	const filters: RecipeFilter[] = [];
	// Iterate over the map to create filter objects
	filterMap.forEach((values, label) => {
		// AND operator
		const valuesToCombineWithAnd = values
			.filter((v) => v.length === 1)
			.map((v) => v[0]);

		if (valuesToCombineWithAnd.length > 0) {
			try {
				filters.push(
					createFilter(
						label,
						valuesToCombineWithAnd.map((t) =>
							normalizeFilterTerm(t),
						),
						new AndOperator(),
					),
				);

				// Update search string and remove created filters
				valuesToCombineWithAnd.forEach((value) => {
					updatedSearchString = updatedSearchString.replace(
						`${label}:${value}`,
						'',
					);
					updatedSearchString = updatedSearchString.replace(
						`${label}:"${value}"`,
						'',
					);
				});
			} catch {
				// No filter created, do not update search string
			}
		}

		// OR operator
		const remainingValues = values.filter((v) => v.length > 1);

		remainingValues.forEach((value) => {
			try {
				filters.push(
					createFilter(
						label,
						value.map((t) => normalizeFilterTerm(t)),
						new OrOperator(),
					),
				);

				// Update search string and remove created filters
				updatedSearchString = updatedSearchString.replace(
					`${label}:${value}`,
					'',
				);
			} catch {
				// No filter created, do not update search string
			}
		});
	});

	return { filters, remainingSearchString: updatedSearchString };
}

export default function parseSearchString(searchString: string) {
	// Extract pairs of labels with values
	const labelValuePairs = extractLabelValuePairs(searchString);

	// Create filters from pairs
	const { filters, remainingSearchString } = createFilters(
		labelValuePairs,
		searchString,
	);

	if (remainingSearchString.trim() !== '') {
		filters.push(
			new RecipeSearchFilter(
				normalizeFilterTerm(remainingSearchString),
				new AndOperator(),
				SearchMode.Fuzzy,
			),
		);
	}

	return filters;
}
