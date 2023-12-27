import RecipeKeywordsFilter from '../../../js/RecipeFilters/RecipeKeywordsFilter';
import { AndOperator, OrOperator } from '../../../js/LogicOperators';

/**
 * Test suite for the RecipeKeywordsFilter class.
 */
describe('RecipeKeywordsFilter', () => {
    /** @type {Object[]} recipes - Array of recipe objects for testing. */
    const recipes = [
        { keywords: ['easy', 'quick', 'pasta'] },
        { keywords: ['healthy', 'salad'] },
        { keywords: 'dessert' },
        { keywords: ['vegetarian', 'pizza'] },
        { keywords: ['salami', 'pizza'] },
        { keywords: 'pizza' },
        { keywords: ['italian', 'pasta', 'spaghetti'] },
        { keywords: 'breakfast' },
        { keywords: ['chocolate', 'cake'] },
        { keywords: ['chocolate', 'cake', 'almond'] },
        { keywords: ['chocolate', 'soufflé'] },
        { keywords: ['lemon', 'cake'] },
        { keywords: 'cake' },
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
     * Test case: it should filter recipes by a single keyword using OR operator.
     */
    test('it should filter recipes by a single keyword using OR operator', () => {
        const filter = new RecipeKeywordsFilter('pizza', new OrOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(3);
        expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
            ['vegetarian', 'pizza'],
            ['salami', 'pizza'],
            'pizza',
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
        expect(filteredRecipes[0].keywords).toEqual(['chocolate', 'cake']);
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
        expect(filteredRecipes).toHaveLength(5);
        expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
            ['chocolate', 'cake'],
            ['chocolate', 'cake', 'almond'],
            ['chocolate', 'soufflé'],
            ['lemon', 'cake'],
            'cake',
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
        expect(filteredRecipes[0].keywords).toBe('dessert');
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
            'dessert',
            ['vegetarian', 'pizza'],
            ['salami', 'pizza'],
            'pizza',
            ['italian', 'pasta', 'spaghetti'],
            'breakfast',
            ['chocolate', 'cake'],
            ['chocolate', 'cake', 'almond'],
            ['chocolate', 'soufflé'],
            ['lemon', 'cake'],
            'cake',
        ]);
    });

    /**
     * Test case: it should handle empty keywords list and return all recipes with AND operator.
     */
    test('it should handle empty keywords list and return no recipes with AND operator', () => {
        const filter = new RecipeKeywordsFilter([], new AndOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(recipes.length);
        expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
            ['easy', 'quick', 'pasta'],
            ['healthy', 'salad'],
            'dessert',
            ['vegetarian', 'pizza'],
            ['salami', 'pizza'],
            'pizza',
            ['italian', 'pasta', 'spaghetti'],
            'breakfast',
            ['chocolate', 'cake'],
            ['chocolate', 'cake', 'almond'],
            ['chocolate', 'soufflé'],
            ['lemon', 'cake'],
            'cake',
        ]);
    });

    /**
     * Test case: it should handle empty-string keywords and return all recipes with OR operator.
     */
    test('it should handle empty keywords and return all recipes with OR operator', () => {
        const filter = new RecipeKeywordsFilter([''], new OrOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(recipes.length);
        expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
            ['easy', 'quick', 'pasta'],
            ['healthy', 'salad'],
            'dessert',
            ['vegetarian', 'pizza'],
            ['salami', 'pizza'],
            'pizza',
            ['italian', 'pasta', 'spaghetti'],
            'breakfast',
            ['chocolate', 'cake'],
            ['chocolate', 'cake', 'almond'],
            ['chocolate', 'soufflé'],
            ['lemon', 'cake'],
            'cake',
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
            'dessert',
            ['vegetarian', 'pizza'],
            ['salami', 'pizza'],
            'pizza',
            ['italian', 'pasta', 'spaghetti'],
            'breakfast',
            ['chocolate', 'cake'],
            ['chocolate', 'cake', 'almond'],
            ['chocolate', 'soufflé'],
            ['lemon', 'cake'],
            'cake',
        ]);
    });

    /**
     * Test case: it should filter recipes when keywords property is a string using AND operator.
     */
    test('it should filter recipes when keywords property is a string using AND operator', () => {
        const filter = new RecipeKeywordsFilter(
            'vegetarian',
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(1);
        expect(filteredRecipes[0].keywords).toEqual(['vegetarian', 'pizza']);
    });

    /**
     * Test case: it should filter recipes when keywords property is a string using OR operator.
     */
    test('it should filter recipes when keywords property is a string using OR operator', () => {
        const filter = new RecipeKeywordsFilter('pizza', new OrOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(3);
        expect(filteredRecipes.map((recipe) => recipe.keywords)).toEqual([
            ['vegetarian', 'pizza'],
            ['salami', 'pizza'],
            'pizza',
        ]);
    });
});
