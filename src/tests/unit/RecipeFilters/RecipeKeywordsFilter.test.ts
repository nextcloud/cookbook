import RecipeKeywordsFilter from 'cookbook/js/RecipeFilters/RecipeKeywordsFilter';
import { AndOperator, OrOperator } from 'cookbook/js/LogicOperators';
import { Recipe } from 'cookbook/js/Models/schema';

function createRecipeWithKeywords(keywords: string[]): Recipe {
	const recipe = new Recipe('123', 'recipe');
	recipe.keywords = keywords;
	return recipe;
}

/**
 * Test suite for the RecipeKeywordsFilter class.
 */
describe('RecipeKeywordsFilter', () => {
	/** @type {Object[]} recipes - Array of recipe objects for testing. */
	const recipes: Recipe[] = [
		createRecipeWithKeywords(['easy', 'quick', 'pasta']),
		createRecipeWithKeywords(['healthy', 'salad']),
		createRecipeWithKeywords(['vegetarian', 'pizza']),
		createRecipeWithKeywords(['salami', 'pizza']),
		createRecipeWithKeywords(['italian', 'pasta', 'spaghetti']),
		createRecipeWithKeywords(['chocolate', 'cake']),
		createRecipeWithKeywords(['chocolate', 'cake', 'almond']),
		createRecipeWithKeywords(['chocolate', 'soufflé']),
		createRecipeWithKeywords(['lemon', 'cake']),
		createRecipeWithKeywords(['soup', 'tomato']),
		createRecipeWithKeywords(['soup', 'carrot', 'lentils']),
		createRecipeWithKeywords(['dessert']),
	];

	/**
	 * Test case: it should filter recipes by a single keyword using AND operator.
	 */
	test('it should filter recipes by a single keyword using AND operator', () => {
		const filter = new RecipeKeywordsFilter('pasta', new AndOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes[0].keywords).toEqual(['easy', 'quick', 'pasta']);
		expect(filteredRecipes[1].keywords).toEqual([
			'italian',
			'pasta',
			'spaghetti',
		]);
	});

	/**
	 * Test case: it should be constructed correctly
	 */
	test('it should be constructed correctly', () => {
		const filter = new RecipeKeywordsFilter('great pasta');
		const filter2 = new RecipeKeywordsFilter(['great pasta']);
		const filter3 = new RecipeKeywordsFilter(['great pasta', 'tasty']);
		expect(filter.keywords).toEqual(['great pasta']);
		expect(filter2.keywords).toEqual(['great pasta']);
		expect(filter3.keywords).toEqual(['great pasta', 'tasty']);
	});

	/**
	 * Test case: it should filter recipes by a single keyword using OR operator.
	 */
	test('it should filter recipes by a single keyword using OR operator', () => {
		const filter = new RecipeKeywordsFilter('pizza', new OrOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['vegetarian', 'pizza'],
			['salami', 'pizza'],
		]);
	});

	/**
	 * Test case: it should filter recipes by multiple keywords using AND operator.
	 */
	test('it should filter recipes by multiple keywords using AND operator', () => {
		const filter = new RecipeKeywordsFilter(
			['chocolate', 'cake'],
			new AndOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['chocolate', 'cake'],
			['chocolate', 'cake', 'almond'],
		]);
	});

	/**
	 * Test case: it should filter recipes by multiple keywords using OR operator.
	 */
	test('it should filter recipes by multiple keywords using OR operator', () => {
		const filter = new RecipeKeywordsFilter(
			['chocolate', 'cake'],
			new OrOperator(),
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(4);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['chocolate', 'cake'],
			['chocolate', 'cake', 'almond'],
			['chocolate', 'soufflé'],
			['lemon', 'cake'],
		]);
	});

	/**
	 * Test case: it should filter recipes by multiple comma-separated keywords using AND operator.
	 */
	test('it should filter recipes by multiple comma-separated keywords using AND operator', () => {
		const filter = new RecipeKeywordsFilter(
			['soup', 'carrot'],
			new AndOperator(),
			true,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['soup', 'carrot', 'lentils'],
		]);
	});

	/**
	 * Test case: it should filter recipes by multiple comma-separated keywords using OR operator.
	 */
	test('it should filter recipes by multiple comma-separated keywords using OR operator', () => {
		const filter = new RecipeKeywordsFilter(
			['soup', 'carrot'],
			new OrOperator(),
			true,
		);
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(2);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['soup', 'tomato'],
			['soup', 'carrot', 'lentils'],
		]);
	});

	/**
	 * Test case: it should handle case-insensitive filtering.
	 */
	test('it should handle case-insensitive filtering', () => {
		const filter = new RecipeKeywordsFilter('DEssERT', new AndOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(1);
		expect(filteredRecipes[0].keywords).toStrictEqual(['dessert']);
	});

	/**
	 * Test case: it should handle empty keywords list and return all recipes with OR operator.
	 */
	test('it should handle empty keywords list and return all recipes with OR operator', () => {
		const filter = new RecipeKeywordsFilter([], new OrOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['easy', 'quick', 'pasta'],
			['healthy', 'salad'],
			['vegetarian', 'pizza'],
			['salami', 'pizza'],
			['italian', 'pasta', 'spaghetti'],
			['chocolate', 'cake'],
			['chocolate', 'cake', 'almond'],
			['chocolate', 'soufflé'],
			['lemon', 'cake'],
			['soup', 'tomato'],
			['soup', 'carrot', 'lentils'],
			['dessert'],
		]);
	});

	/**
	 * Test case: it should handle empty keywords list and return all recipes with AND operator.
	 */
	test('it should handle empty keywords list and return all recipes with AND operator', () => {
		const filter = new RecipeKeywordsFilter([], new AndOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['easy', 'quick', 'pasta'],
			['healthy', 'salad'],
			['vegetarian', 'pizza'],
			['salami', 'pizza'],
			['italian', 'pasta', 'spaghetti'],
			['chocolate', 'cake'],
			['chocolate', 'cake', 'almond'],
			['chocolate', 'soufflé'],
			['lemon', 'cake'],
			['soup', 'tomato'],
			['soup', 'carrot', 'lentils'],
			['dessert'],
		]);
	});

	/**
	 * Test case: it should handle empty-string keywords and return all recipes with OR operator.
	 */
	test('it should handle empty-string keywords and return all recipes with OR operator', () => {
		const filter = new RecipeKeywordsFilter([''], new OrOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['easy', 'quick', 'pasta'],
			['healthy', 'salad'],
			['vegetarian', 'pizza'],
			['salami', 'pizza'],
			['italian', 'pasta', 'spaghetti'],
			['chocolate', 'cake'],
			['chocolate', 'cake', 'almond'],
			['chocolate', 'soufflé'],
			['lemon', 'cake'],
			['soup', 'tomato'],
			['soup', 'carrot', 'lentils'],
			['dessert'],
		]);
	});

	/**
	 * Test case: it should handle empty-string keywords and return all recipes with AND operator.
	 */
	test('it should handle empty-string keywords and return no recipes with AND operator', () => {
		const filter = new RecipeKeywordsFilter([''], new AndOperator());
		const filteredRecipes = recipes.filter((recipe) =>
			filter.filter(recipe),
		);
		expect(filteredRecipes).toHaveLength(recipes.length);
		expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
			['easy', 'quick', 'pasta'],
			['healthy', 'salad'],
			['vegetarian', 'pizza'],
			['salami', 'pizza'],
			['italian', 'pasta', 'spaghetti'],
			['chocolate', 'cake'],
			['chocolate', 'cake', 'almond'],
			['chocolate', 'soufflé'],
			['lemon', 'cake'],
			['soup', 'tomato'],
			['soup', 'carrot', 'lentils'],
			['dessert'],
		]);
	});

	/**
	 * Test case: it should return correct search string for AND operator.
	 */
	test('it should return correct search string for AND operator.', () => {
		const filter = new RecipeKeywordsFilter(
			['pizza', 'pasta'],
			new AndOperator(),
		);
		const filter2 = new RecipeKeywordsFilter(['pizza'], new AndOperator());
		const filter3 = new RecipeKeywordsFilter([], new AndOperator());

		expect(filter.toSearchString()).toEqual('tag:"pizza" tag:"pasta"');
		expect(filter2.toSearchString()).toEqual('tag:"pizza"');
		expect(filter3.toSearchString()).toEqual('');
	});

	/**
	 * Test case: it should return correct search string for OR operator.
	 */
	test('it should return correct search string for OR operator.', () => {
		const filter = new RecipeKeywordsFilter(
			['pizza', 'pasta'],
			new OrOperator(),
		);
		const filter2 = new RecipeKeywordsFilter(['pizza'], new OrOperator());
		const filter3 = new RecipeKeywordsFilter([], new OrOperator());

		expect(filter.toSearchString()).toEqual('tag:"pizza","pasta"');
		expect(filter2.toSearchString()).toEqual('tag:"pizza"');
		expect(filter3.toSearchString()).toEqual('');
	});

	describe('equals()', () => {
		// Test for filters that should be equal
		test('should return true for equivalent filters', () => {
			const filter1 = new RecipeKeywordsFilter(
				['chicken', 'grilled'],
				new AndOperator(),
				false,
			);
			const filter2 = new RecipeKeywordsFilter(
				['grilled', 'chicken'],
				new AndOperator(),
				false,
			);

			expect(filter1.equals(filter2)).toBe(true);
		});
		// Test for filters that should be equal
		test('should return true for same filters', () => {
			const filter1 = new RecipeKeywordsFilter(
				['grilled', 'chicken'],
				new AndOperator(),
				false,
			);
			const filter2 = new RecipeKeywordsFilter(
				['grilled', 'chicken'],
				new AndOperator(),
				false,
			);

			expect(filter1.equals(filter2)).toBe(true);
		});

		// Test for filters with different keywords
		test('should return false for filters with different keywords', () => {
			const filter1 = new RecipeKeywordsFilter(
				['chicken'],
				new AndOperator(),
				false,
			);
			const filter2 = new RecipeKeywordsFilter(
				['beef'],
				new AndOperator(),
				false,
			);

			expect(filter1.equals(filter2)).toBe(false);
		});

		// Test for filters with different operators
		test('should return false for filters with different operators and multiple keywords', () => {
			const filter1 = new RecipeKeywordsFilter(
				['chicken', 'eggs'],
				new AndOperator(),
				false,
			);
			const filter2 = new RecipeKeywordsFilter(
				['chicken', 'eggs'],
				new OrOperator(),
				false,
			);

			expect(filter1.equals(filter2)).toBe(false);
		});

		// Test for filters with different operators
		test('should return true for filters with different operators and single keyword', () => {
			const filter1 = new RecipeKeywordsFilter(
				['chicken'],
				new AndOperator(),
				false,
			);
			const filter2 = new RecipeKeywordsFilter(
				['chicken'],
				new OrOperator(),
				false,
			);

			expect(filter1.equals(filter2)).toBe(true);
		});

		// Test for filters with different isCommaSeparated values
		test('should return false for filters with different isCommaSeparated values', () => {
			const filter1 = new RecipeKeywordsFilter(
				['chicken', 'grilled'],
				new AndOperator(),
				false,
			);
			const filter2 = new RecipeKeywordsFilter(
				['chicken', 'grilled'],
				new AndOperator(),
				true,
			);

			expect(filter1.equals(filter2)).toBe(false);
		});

		/**
		 * Test case: it should handle comparison with equal filters correctly.
		 */
		test('it should handle comparison with equal filters correctly', () => {
			const filter = new RecipeKeywordsFilter(
				['sweet', 'salty'],
				new AndOperator(),
			);
			const sameFilter = new RecipeKeywordsFilter(
				['sweet', 'salty'],
				new AndOperator(),
			);

			expect(filter.equals(sameFilter)).toBeTruthy();
		});

		/**
		 * Test case: it should handle comparison with unequal filters correctly.
		 */
		test('it should handle comparison with unequal filters correctly', () => {
			const filter = new RecipeKeywordsFilter(
				['sweet', 'salty'],
				new AndOperator(),
			);
			const differentFilter = new RecipeKeywordsFilter(
				['sweet'],
				new AndOperator(),
			);
			const differentFilter2 = new RecipeKeywordsFilter(
				['sweet', 'hot'],
				new AndOperator(),
			);
			const differentFilter3 = new RecipeKeywordsFilter(
				['sweet', 'salty'],
				new OrOperator(),
			);

			expect(filter.equals(differentFilter)).toBeFalsy();
			expect(filter.equals(differentFilter2)).toBeFalsy();
			expect(filter.equals(differentFilter3)).toBeFalsy();
		});
	});
});
