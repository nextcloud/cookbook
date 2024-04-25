import parseSearchString from 'cookbook/js/utils/parseSearchString'; // Adjust the import based on your actual file structure
import { AndOperator, OrOperator } from 'cookbook/js/LogicOperators';
import {
	RecipeKeywordsFilter as KeywordFilter,
	RecipeCategoriesFilter as CategoryFilter,
	RecipeSearchFilter as SearchFilter,
} from 'cookbook/js/RecipeFilters';

describe('parseSearchString', () => {
	test('should correctly parse a single item filter', () => {
		const searchString = 'tag:"sweet"';
		const filters = parseSearchString(searchString);

		// Expect a single TagFilter object
		expect(filters.length).toBe(1);
		expect(filters[0]).toBeInstanceOf(KeywordFilter);

		// Check the TagFilter properties
		expect((filters[0] as KeywordFilter).keywords).toEqual(['sweet']);
		expect(filters[0].operator).toBeInstanceOf(AndOperator);
	});

	test('should correctly parse a single item with space filter', () => {
		const searchString = 'tag:"sweet sauce"';
		const filters = parseSearchString(searchString);

		// Expect a single TagFilter object
		expect(filters.length).toBe(1);
		expect(filters[0]).toBeInstanceOf(KeywordFilter);

		// Check the TagFilter properties
		expect((filters[0] as KeywordFilter).keywords).toEqual(['sweet sauce']);
		expect(filters[0].operator).toBeInstanceOf(AndOperator);
	});

	test('should correctly parse two items with different labels', () => {
		const searchString = 'tag:"sweet" cat:"Dessert"';
		const filters = parseSearchString(searchString);

		// Expect two filter objects: one TagFilter and one CategoryFilter
		expect(filters.length).toBe(2);
		expect(filters[0]).toBeInstanceOf(KeywordFilter);
		expect(filters[1]).toBeInstanceOf(CategoryFilter);

		// Check the TagFilter properties
		expect((filters[0] as KeywordFilter).keywords).toEqual(['sweet']);
		expect(filters[0].operator).toBeInstanceOf(AndOperator);

		// Check the CategoryFilter properties
		expect((filters[1] as CategoryFilter).categories).toEqual(['Dessert']);
		expect(filters[1].operator).toBeInstanceOf(AndOperator);
	});

	test('should correctly parse a single item with OR filter', () => {
		const searchString = 'tag:"sweet","salty"';
		const filters = parseSearchString(searchString);

		// Expect a single TagFilter object
		expect(filters.length).toBe(1);
		expect(filters[0]).toBeInstanceOf(KeywordFilter);

		// Check the TagFilter properties
		expect((filters[0] as KeywordFilter).keywords).toEqual([
			'sweet',
			'salty',
		]);
		expect(filters[0].operator).toBeInstanceOf(OrOperator);
	});

	test('should correctly parse a single item with mixed quoted and unquoted terms in OR filter', () => {
		const searchString = 'tag:"sweet",salty';
		const filters = parseSearchString(searchString);

		// Expect a single TagFilter object
		expect(filters.length).toBe(1);
		expect(filters[0]).toBeInstanceOf(KeywordFilter);

		// Check the TagFilter properties
		expect((filters[0] as KeywordFilter).keywords).toEqual([
			'sweet',
			'salty',
		]);
		expect(filters[0].operator).toBeInstanceOf(OrOperator);
	});

	test('should correctly parse a two items with AND filter', () => {
		const searchString = 'tag:"sweet" tag:"salty"';
		const filters = parseSearchString(searchString);

		// Expect a single TagFilter object
		expect(filters.length).toBe(1);
		expect(filters[0]).toBeInstanceOf(KeywordFilter);

		// Check the TagFilter properties
		expect((filters[0] as KeywordFilter).keywords).toEqual([
			'sweet',
			'salty',
		]);
		expect(filters[0].operator).toBeInstanceOf(AndOperator);
	});

	test('should correctly parse mixed AND and OR filters', () => {
		const searchString = 'tag:"sweet","salty" tag:"quick"';
		const filters = parseSearchString(searchString);

		// Expect two TagFilter objects
		expect(filters.length).toBe(2);
		expect(filters[0]).toBeInstanceOf(KeywordFilter);
		expect(filters[1]).toBeInstanceOf(KeywordFilter);

		// Check the second filter (AND logic)
		expect((filters[0] as KeywordFilter).keywords).toEqual(['quick']);
		expect(filters[0].operator).toBeInstanceOf(AndOperator);

		// Check the first filter (OR logic)
		expect((filters[1] as KeywordFilter).keywords).toEqual([
			'sweet',
			'salty',
		]);
		expect(filters[1].operator).toBeInstanceOf(OrOperator);
	});

	test('should correctly parse unlabelled search terms', () => {
		const searchString = 'cat:Dessert Search query';
		const filters = parseSearchString(searchString);

		// Expect a CategoryFilter and a SearchFilter
		expect(filters.length).toBe(2);
		expect(filters[0]).toBeInstanceOf(CategoryFilter);
		expect(filters[1]).toBeInstanceOf(SearchFilter);

		// Check the CategoryFilter
		expect((filters[0] as CategoryFilter).categories).toEqual(['Dessert']);
		expect(filters[0].operator).toBeInstanceOf(AndOperator);

		// Check the SearchFilter
		expect((filters[1] as SearchFilter).queries).toStrictEqual([
			'Search query',
		]);
		expect(filters[1].operator).toBeInstanceOf(AndOperator);
	});

	test('should correctly parse unknown label as search terms', () => {
		// Prepare
		const searchString1 = 'unknown:Dessert';
		const searchString2 = 'unknown:';
		const searchString3 = 'cat:"Main dish" unknown:Dessert';

		// Act
		const filters = parseSearchString(searchString1);
		const filters2 = parseSearchString(searchString2);
		const filters3 = parseSearchString(searchString3);

		// Assert

		// Expect a SearchFilter
		expect(filters.length).toBe(1);
		expect(filters[0]).toBeInstanceOf(SearchFilter);

		// Check the SearchFilter
		expect((filters[0] as SearchFilter).queries).toStrictEqual([
			'unknown:Dessert',
		]);

		// Expect a SearchFilter
		expect(filters2.length).toBe(1);
		expect(filters2[0]).toBeInstanceOf(SearchFilter);

		// Check the SearchFilter
		expect((filters2[0] as SearchFilter).queries).toStrictEqual([
			'unknown:',
		]);

		// Expect a CategoryFilter and a SearchFilter
		expect(filters3.length).toBe(2);
		expect(filters3[0]).toBeInstanceOf(CategoryFilter);
		expect(filters3[1]).toBeInstanceOf(SearchFilter);

		// Check the CategoryFilter
		expect((filters3[0] as CategoryFilter).categories).toStrictEqual([
			'Main dish',
		]);

		// Check the SearchFilter
		expect((filters3[1] as SearchFilter).queries).toStrictEqual([
			'unknown:Dessert',
		]);
	});
});
