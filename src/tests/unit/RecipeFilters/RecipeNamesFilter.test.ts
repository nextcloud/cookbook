import RecipeNamesFilter from 'cookbook/js/RecipeFilters/RecipeNamesFilter';
import { AndOperator, OrOperator } from 'cookbook/js/LogicOperators';
import { Recipe } from 'cookbook/js/Models/schema';
import SearchMode from 'cookbook/js/Enums/SearchMode';

/**
 * Test suite for the RecipeNamesFilter class.
 */
describe('RecipeNamesFilter', () => {
	/** @type {Object[]} recipes - Array of recipe objects for testing. */
	const recipes: Recipe[] = [
		new Recipe('123', 'Pasta Carbonara'),
		new Recipe('223', 'Better Pasta Carbonara'),
		new Recipe('323', 'Chocolate Cake'),
		new Recipe('423', 'Brownie'),
		new Recipe('523', 'Salad'),
		new Recipe('623', 'Pizza Calzone'),
		new Recipe('723', 'Pizza'),
	];

	/**
	 * Test case: it should filter recipes by a single name using AND operator.
	 */
	test('it should filter recipes by a single name using AND operator', () => {
		const filter = new RecipeNamesFilter(
			'pasta carbonara',
			new AndOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);
		expect(filteredRecipes[0].name).toBe('Pasta Carbonara');
		// expect(filteredRecipes[1].name).toStrictEqual('Better Pasta Carbonara');
	});

	/**
	 * Test case: it should filter recipes by a single name using OR operator.
	 */
	test('it should filter recipes by a single name using OR operator', () => {
		const filter = new RecipeNamesFilter('pizza', new OrOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);
		expect(filteredRecipes.map((recipe) => recipe.name)).toEqual(['Pizza']);
	});

	/**
	 * Test case: it should filter recipes by multiple names using AND operator.
	 */
	test('it should filter recipes by multiple names using AND operator', () => {
		// The recipe name is currently not allowed to be an array.
		// Will only return result if both queries are the same name or fuzzy search is on and both match the same recipe.
		const filter = new RecipeNamesFilter(
			['Chocolate Cake'],
			new AndOperator(),
			SearchMode.Exact,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);
		expect(filteredRecipes[0].name).toEqual('Chocolate Cake');
	});

	/**
	 * Test case: it should filter recipes by multiple names using OR operator.
	 */
	test('it should filter recipes by multiple names using OR operator', () => {
		const filter = new RecipeNamesFilter(
			['salad', 'pizza calzone'],
			new OrOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
			'Salad',
			'Pizza Calzone',
		]);
	});

	/**
	 * Test case: it should handle case-insensitive filtering.
	 */
	test('it should handle case-insensitive filtering', () => {
		const filter = new RecipeNamesFilter(
			'PasTA cArBOnara',
			new AndOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);
		expect(filteredRecipes[0].name).toStrictEqual('Pasta Carbonara');
	});

	/**
	 * Test case: it should handle empty names list and return all recipes with OR operator.
	 */
	test('it should handle empty names and return all recipes with OR operator', () => {
		const filter = new RecipeNamesFilter([], new OrOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
			'Chocolate Cake',
			'Brownie',
			'Salad',
			'Pizza Calzone',
			'Pizza',
		]);
	});

	/**
	 * Test case: it should handle empty names list and return all recipes with AND operator.
	 */
	test('it should handle empty names and return all recipes with AND operator', () => {
		const filter = new RecipeNamesFilter([], new AndOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
			'Chocolate Cake',
			'Brownie',
			'Salad',
			'Pizza Calzone',
			'Pizza',
		]);
	});

	/**
	 * Test case: it should handle empty-string names and return all recipes with OR operator.
	 */
	test('it should handle empty-string names and return all recipes with OR operator', () => {
		const filter = new RecipeNamesFilter([''], new OrOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
			'Chocolate Cake',
			'Brownie',
			'Salad',
			'Pizza Calzone',
			'Pizza',
		]);
	});

	/**
	 * Test case: it should handle empty-string names and return all recipes with AND operator.
	 */
	test('it should handle empty-string names and return all recipes with AND operator', () => {
		const filter = new RecipeNamesFilter([''], new AndOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
			'Chocolate Cake',
			'Brownie',
			'Salad',
			'Pizza Calzone',
			'Pizza',
		]);
	});

	/**
	 * Test case: it should filter recipes when name property is an array using AND operator.
	 */
	test('it should filter recipes when name property is an array using AND operator', () => {
		const filter = new RecipeNamesFilter(
			['Chocolate Cake', 'Chocolate Cake'],
			new AndOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);
		expect(filteredRecipes[0].name).toEqual('Chocolate Cake');
	});

	/**
	 * Test case: it should filter recipes when name property is an array using AND operator but fail for parts without fuzzy search.
	 */
	test('it should filter recipes when name property is an array using AND operator but fail for parts without fuzzy search', () => {
		const filter = new RecipeNamesFilter(
			['Chocolate', 'Cake'],
			new AndOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(0);
	});

	/**
	 * Test case: it should filter recipes when name property is an array using OR operator.
	 */
	test('it should filter recipes when name property is an array using OR operator', () => {
		const filter = new RecipeNamesFilter(
			['salad', 'pizza calzone'],
			new OrOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
			'Salad',
			'Pizza Calzone',
		]);
	});

	/**
	 * Test case: it should filter recipes if part of name is included using AND operator, matchSubstring mode.
	 */
	test('it should filter recipes if part of name is included using AND operator, matchSubstring mode.', () => {
		const filter = new RecipeNamesFilter(
			'pasta',
			new AndOperator(),
			SearchMode.MatchSubstring,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
		]);
	});

	/**
	 * Test case: it should filter recipes if part of name is included using OR operator, matchSubstring mode.
	 */
	test('it should filter recipes if part of name is included using OR operator, matchSubstring mode.', () => {
		const filter = new RecipeNamesFilter(
			'pasta',
			new OrOperator(),
			SearchMode.MatchSubstring,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
		]);
	});

	/**
	 * Test case: it should filter recipes with fuzzy search using AND operator.
	 */
	test('it should filter recipes with fuzzy search using AND operator', () => {
		const filter = new RecipeNamesFilter(
			'Piza Car',
			new AndOperator(),
			SearchMode.Fuzzy,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(3);

		expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
			'Pizza Calzone',
		]);
	});

	/**
	 * Test case: it should filter recipes with fuzzy search, multiple terms, using AND operator.
	 */
	test('it should filter recipes with fuzzy search, multiple terms, using AND operator', () => {
		const filter = new RecipeNamesFilter(
			['Pizz', 'C4lz'],
			new AndOperator(),
			SearchMode.Fuzzy,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);

		expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
			'Pizza Calzone',
		]);
	});

	/**
	 * Test case: it should filter recipes with fuzzy search using OR operator.
	 */
	test('it should filter recipes with fuzzy search using OR operator', () => {
		const filter = new RecipeNamesFilter(
			'Piza Car',
			new OrOperator(),
			SearchMode.Fuzzy,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(3);

		expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
			'Pizza Calzone',
		]);
	});

	/**
	 * Test case: it should filter recipes with fuzzy search, multiple terms, using OR operator.
	 */
	test('it should filter recipes with fuzzy search, multiple terms, using OR operator', () => {
		const filter = new RecipeNamesFilter(
			['Piza Car', 'S4l'],
			new OrOperator(),
			SearchMode.Fuzzy,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(4);

		expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
			'Pasta Carbonara',
			'Better Pasta Carbonara',
			'Salad',
			'Pizza Calzone',
		]);
	});

	describe('equals()', () => {
		/**
		 * Test case: it should handle comparison with equal filters correctly.
		 */
		test('it should handle comparison with equal filters correctly', () => {
			const filter = new RecipeNamesFilter(
				['sweet', 'salty'],
				new AndOperator(),
				SearchMode.Fuzzy,
			);
			const sameFilter = new RecipeNamesFilter(
				['sweet', 'salty'],
				new AndOperator(),
				SearchMode.Fuzzy,
			);

			expect(filter.equals(sameFilter)).toBeTruthy();
		});

		/**
		 * Test case: it should handle comparison with unequal filters correctly.
		 */
		test('it should handle comparison with unequal filters correctly', () => {
			const filter = new RecipeNamesFilter(
				['sweet', 'salty'],
				new AndOperator(),
				SearchMode.Fuzzy,
			);
			const differentFilter = new RecipeNamesFilter(
				['sweet'],
				new AndOperator(),
				SearchMode.Fuzzy,
			);
			const differentFilter2 = new RecipeNamesFilter(
				['sweet', 'hot'],
				new AndOperator(),
				SearchMode.Fuzzy,
			);
			const differentFilter3 = new RecipeNamesFilter(
				['sweet', 'salty'],
				new OrOperator(),
				SearchMode.Fuzzy,
			);
			const differentFilter4 = new RecipeNamesFilter(
				['sweet', 'salty'],
				new OrOperator(),
				SearchMode.Exact,
			);

			expect(filter.equals(differentFilter)).toBeFalsy();
			expect(filter.equals(differentFilter2)).toBeFalsy();
			expect(filter.equals(differentFilter3)).toBeFalsy();
			expect(filter.equals(differentFilter4)).toBeFalsy();
		});
	});
});
