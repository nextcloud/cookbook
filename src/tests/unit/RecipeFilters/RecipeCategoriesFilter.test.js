import RecipeCategoriesFilter from '../../../js/RecipeFilters/RecipeCategoriesFilter';
import { AndOperator, OrOperator } from '../../../js/LogicOperators';

/**
 * Test suite for the RecipeCategoriesFilter class.
 */
describe('RecipeCategoriesFilter', () => {
    /** @type {Object[]} recipes - Array of recipe objects for testing. */
    const recipes = [
        { recipeCategory: 'main course' },
        { recipeCategory: ['salad', 'appetizer'] },
        { recipeCategory: 'dessert' },
        { recipeCategory: ['vegetarian', 'pizza'] },
        { recipeCategory: ['pizza', 'appetizer'] },
        { recipeCategory: 'pizza' },
        { recipeCategory: ['pasta', 'main course'] },
        { recipeCategory: 'breakfast' },
        { recipeCategory: ['cake', 'dessert'] },
        { recipeCategory: ['cake', 'dessert', 'gluten-free'] },
        { recipeCategory: ['soufflé', 'dessert'] },
        { recipeCategory: ['cake', 'gluten-free'] },
        { recipeCategory: 'cake' },
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
        expect(filteredRecipes[0].recipeCategory).toBe('main course');
        expect(filteredRecipes[1].recipeCategory).toEqual([
            'pasta',
            'main course',
        ]);
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
        expect(filteredRecipes.map((recipe) => recipe.recipeCategory)).toEqual([
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
        expect(filteredRecipes[0].recipeCategory).toBe('breakfast');
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
        expect(filteredRecipes[0].recipeCategory).toBe('breakfast');
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
        expect(filteredRecipes[0].recipeCategory).toEqual(['cake', 'dessert']);
        expect(filteredRecipes.map((recipe) => recipe.recipeCategory)).toEqual([
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
        expect(filteredRecipes.map((recipe) => recipe.recipeCategory)).toEqual([
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
        expect(filteredRecipes[0].recipeCategory).toBe('breakfast');
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
        expect(filteredRecipes.map((recipe) => recipe.recipeCategory)).toEqual([
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
        expect(filteredRecipes.map((recipe) => recipe.recipeCategory)).toEqual([
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
        expect(filteredRecipes.map((recipe) => recipe.recipeCategory)).toEqual([
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
        expect(filteredRecipes.map((recipe) => recipe.recipeCategory)).toEqual([
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
