// eslint-disable-next-line max-classes-per-file
import applyFilters from '../../../js/utils/applyRecipeFilters';
import RecipeFilter from '../../../js/RecipeFilters/RecipeFilter';
import { AndOperator } from '../../../js/LogicOperators';
// Mocked filter classes
class MockFilter extends RecipeFilter {
	constructor(predicate) {
		super(new AndOperator());
		this.filter = jest.fn(predicate);
	}
}

/**
 * Test suite for the applyFilters method.
 */
describe('applyFilters', () => {
	/** @type {Object[]} recipes - Array of recipe objects for testing. */
	const recipes = [
		{
			name: 'Pasta Carbonara',
			keywords: ['pasta', 'italian'],
			recipeCategory: ['main course'],
		},
		{
			name: 'Pasta Carbonara',
			keywords: ['italian'],
			recipeCategory: ['main course'],
		},
		{
			name: 'Pasta Carbonara',
			keywords: ['pasta', 'creamy'],
			recipeCategory: ['main course'],
		},
		{
			name: 'Chocolate Cake',
			keywords: ['chocolate', 'cake'],
			recipeCategory: ['dessert'],
		},
		{
			name: 'Salad',
			keywords: ['healthy', 'salad'],
			recipeCategory: ['appetizer'],
		},
	];

	/**
	 * Test case: it should apply mocked filters and filter recipes correctly.
	 */
	test('it should apply mocked filters and filter recipes correctly', () => {
		// Create mocked filters
		const filter1 = new MockFilter(
			(recipe) => recipe.name === 'Pasta Carbonara',
		);
		const filter2 = new MockFilter((recipe) =>
			recipe.keywords.includes('pasta'),
		);

		// Apply filters
		const filteredRecipes = applyFilters(recipes, [filter1, filter2]);

		// Check if filter methods were called
		expect(filter1.filter).toHaveBeenCalledTimes(5);
		expect(filter2.filter).toHaveBeenCalledTimes(3);

		// Check if recipes are filtered correctly
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['pasta', 'italian'],
			['pasta', 'creamy'],
		]);
	});

	/**
	 * Test case: it should handle an empty array of filters and return all recipes.
	 */
	test('it should handle an empty array of filters and return all recipes', () => {
		// Apply filters with an empty array
		const filteredRecipes = applyFilters(recipes, []);

		// Check if all recipes are returned
		expect(filteredRecipes).toHaveLength(5);
		expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
			'Pasta Carbonara',
			'Pasta Carbonara',
			'Pasta Carbonara',
			'Chocolate Cake',
			'Salad',
		]);
	});
});
