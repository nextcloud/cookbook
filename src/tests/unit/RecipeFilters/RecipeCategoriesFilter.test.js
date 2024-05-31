import RecipeCategoriesFilter from '../../../js/RecipeFilters/RecipeCategoriesFilter';
import { AndOperator, OrOperator } from '../../../js/LogicOperators';

/**
 * Test suite for the RecipeCategoriesFilter class.
 */
describe('RecipeCategoriesFilter', () => {
    /** @type {Object[]} recipes - Array of recipe objects for testing. */
    const recipes = [
        { category: 'main course' },
        { category: ['salad', 'appetizer'] },
        { category: 'dessert' },
        { category: ['vegetarian', 'pizza'] },
        { category: ['pizza', 'appetizer'] },
        { category: 'pizza' },
        { category: ['pasta', 'main course'] },
        { category: 'breakfast' },
        { category: ['cake', 'dessert'] },
        { category: ['cake', 'dessert', 'gluten-free'] },
        { category: ['soufflé', 'dessert'] },
        { category: ['cake', 'gluten-free'] },
        { category: 'cake' },
    ];

    /**
     * Test case: it should filter recipes by a single category using AND operator.
     */
    test('it should filter recipes by a single category using AND operator', () => {
        const filter = new RecipeCategoriesFilter(
            'main course',
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(2);
        expect(filteredRecipes[0].category).toBe('main course');
        expect(filteredRecipes[1].category).toEqual(['pasta', 'main course']);
    });

    /**
     * Test case: it should filter recipes by a single category using OR operator.
     */
    test('it should filter recipes by a single category using OR operator', () => {
        const filter = new RecipeCategoriesFilter('pizza', new OrOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(3);
        expect(filteredRecipes.map((recipe) => recipe.category)).toEqual([
            ['vegetarian', 'pizza'],
            ['pizza', 'appetizer'],
            'pizza',
        ]);
    });

    /**
     * Test case: it should filter recipes when categories property is a string using AND operator.
     */
    test('it should filter recipes when categories property is a string using AND operator', () => {
        const filter = new RecipeCategoriesFilter(
            'breakfast',
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(1);
        expect(filteredRecipes[0].category).toBe('breakfast');
    });

    /**
     * Test case: it should filter recipes when categories property is a string using OR operator.
     */
    test('it should filter recipes when categories property is a string using OR operator', () => {
        const filter = new RecipeCategoriesFilter(
            'breakfast',
            new OrOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(1);
        expect(filteredRecipes[0].category).toBe('breakfast');
    });

    /**
     * Test case: it should filter recipes by multiple categories using AND operator.
     */
    test('it should filter recipes by multiple categories using AND operator', () => {
        const filter = new RecipeCategoriesFilter(
            ['cake', 'dessert'],
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(2);
        expect(filteredRecipes[0].category).toEqual(['cake', 'dessert']);
        expect(filteredRecipes.map((recipe) => recipe.category)).toEqual([
            ['cake', 'dessert'],
            ['cake', 'dessert', 'gluten-free'],
        ]);
    });

    /**
     * Test case: it should filter recipes by multiple categories using OR operator.
     */
    test('it should filter recipes by multiple categories using OR operator', () => {
        const filter = new RecipeCategoriesFilter(
            ['breakfast', 'dessert'],
            new OrOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(5);
        expect(filteredRecipes.map((recipe) => recipe.category)).toEqual([
            'dessert',
            'breakfast',
            ['cake', 'dessert'],
            ['cake', 'dessert', 'gluten-free'],
            ['soufflé', 'dessert'],
        ]);
    });

    /**
     * Test case: it should handle case-insensitive filtering.
     */
    test('it should handle case-insensitive filtering', () => {
        const filter = new RecipeCategoriesFilter(
            'bREAKfast',
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(1);
        expect(filteredRecipes[0].category).toBe('breakfast');
    });

    /**
     * Test case: it should handle empty categories list and return all recipes with OR operator.
     */
    test('it should handle empty categories list and return all recipes with OR operator', () => {
        const filter = new RecipeCategoriesFilter([], new OrOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(recipes.length);
        expect(filteredRecipes.map((recipe) => recipe.category)).toEqual([
            'main course',
            ['salad', 'appetizer'],
            'dessert',
            ['vegetarian', 'pizza'],
            ['pizza', 'appetizer'],
            'pizza',
            ['pasta', 'main course'],
            'breakfast',
            ['cake', 'dessert'],
            ['cake', 'dessert', 'gluten-free'],
            ['soufflé', 'dessert'],
            ['cake', 'gluten-free'],
            'cake',
        ]);
    });

    /**
     * Test case: it should handle empty categories list and return all recipes with AND operator.
     */
    test('it should handle empty categories list and return no recipes with AND operator', () => {
        const filter = new RecipeCategoriesFilter([], new AndOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(recipes.length);
        expect(filteredRecipes.map((recipe) => recipe.category)).toEqual([
            'main course',
            ['salad', 'appetizer'],
            'dessert',
            ['vegetarian', 'pizza'],
            ['pizza', 'appetizer'],
            'pizza',
            ['pasta', 'main course'],
            'breakfast',
            ['cake', 'dessert'],
            ['cake', 'dessert', 'gluten-free'],
            ['soufflé', 'dessert'],
            ['cake', 'gluten-free'],
            'cake',
        ]);
    });

    /**
     * Test case: it should handle empty-string categories and return all recipes with OR operator.
     */
    test('it should handle empty-string categories and return all recipes with OR operator', () => {
        const filter = new RecipeCategoriesFilter([''], new OrOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(recipes.length);
        expect(filteredRecipes.map((recipe) => recipe.category)).toEqual([
            'main course',
            ['salad', 'appetizer'],
            'dessert',
            ['vegetarian', 'pizza'],
            ['pizza', 'appetizer'],
            'pizza',
            ['pasta', 'main course'],
            'breakfast',
            ['cake', 'dessert'],
            ['cake', 'dessert', 'gluten-free'],
            ['soufflé', 'dessert'],
            ['cake', 'gluten-free'],
            'cake',
        ]);
    });

    /**
     * Test case: it should handle empty-string categories and return all recipes with AND operator.
     */
    test('it should handle empty-string categories and return no recipes with AND operator', () => {
        const filter = new RecipeCategoriesFilter([''], new AndOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(recipes.length);
        expect(filteredRecipes.map((recipe) => recipe.category)).toEqual([
            'main course',
            ['salad', 'appetizer'],
            'dessert',
            ['vegetarian', 'pizza'],
            ['pizza', 'appetizer'],
            'pizza',
            ['pasta', 'main course'],
            'breakfast',
            ['cake', 'dessert'],
            ['cake', 'dessert', 'gluten-free'],
            ['soufflé', 'dessert'],
            ['cake', 'gluten-free'],
            'cake',
        ]);
    });
});
