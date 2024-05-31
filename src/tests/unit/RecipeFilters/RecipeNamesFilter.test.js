import RecipeNamesFilter from '../../../js/RecipeFilters/RecipeNamesFilter';

import { AndOperator, OrOperator } from '../../../js/LogicOperators';

/**
 * Test suite for the RecipeNamesFilter class.
 */
describe('RecipeNamesFilter', () => {
    /** @type {Object[]} recipes - Array of recipe objects for testing. */
    const recipes = [
        { name: 'Pasta Carbonara' },
        { name: ['Pasta Carbonara', 'Italian pasta'] },
        { name: ['Chocolate Cake', 'Brownie'] },
        { name: 'Salad' },
        { name: ['Pizza', 'Calzone', 'Pizza Calzone'] },
        { name: 'Pizza' },
        { name: ['Pizza', 'Pizza Carcassonne'] },
        { name: ['Spaghetti Bolognese', 'Garlic Bread'] },
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
        expect(filteredRecipes).toHaveLength(2);
        expect(filteredRecipes[0].name).toBe('Pasta Carbonara');
        expect(filteredRecipes[1].name).toStrictEqual([
            'Pasta Carbonara',
            'Italian pasta',
        ]);
    });

    /**
     * Test case: it should filter recipes by a single name using OR operator.
     */
    test('it should filter recipes by a single name using OR operator', () => {
        const filter = new RecipeNamesFilter('pizza', new OrOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(3);
        expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
            ['Pizza', 'Calzone', 'Pizza Calzone'],
            'Pizza',
            ['Pizza', 'Pizza Carcassonne'],
        ]);
    });

    /**
     * Test case: it should filter recipes by multiple names using AND operator.
     */
    test('it should filter recipes by multiple names using AND operator', () => {
        const filter = new RecipeNamesFilter(
            ['chocolate cake', 'brownie'],
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(1);
        expect(filteredRecipes[0].name).toEqual(['Chocolate Cake', 'Brownie']);
    });

    /**
     * Test case: it should filter recipes by multiple names using OR operator.
     */
    test('it should filter recipes by multiple names using OR operator', () => {
        const filter = new RecipeNamesFilter(
            ['salad', 'calzone'],
            new OrOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(2);
        expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
            'Salad',
            ['Pizza', 'Calzone', 'Pizza Calzone'],
        ]);
    });

    /**
     * Test case: it should handle case-insensitive filtering.
     */
    test('it should handle case-insensitive filtering', () => {
        const filter = new RecipeNamesFilter(
            'ITALIAN pAsTa',
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(1);
        expect(filteredRecipes[0].name).toStrictEqual([
            'Pasta Carbonara',
            'Italian pasta',
        ]);
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
            ['Pasta Carbonara', 'Italian pasta'],
            ['Chocolate Cake', 'Brownie'],
            'Salad',
            ['Pizza', 'Calzone', 'Pizza Calzone'],
            'Pizza',
            ['Pizza', 'Pizza Carcassonne'],
            ['Spaghetti Bolognese', 'Garlic Bread'],
        ]);
    });

    /**
     * Test case: it should handle empty names list and return all recipes with AND operator.
     */
    test('it should handle empty names and return no recipes with AND operator', () => {
        const filter = new RecipeNamesFilter([], new AndOperator());
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(recipes.length);
        expect(filteredRecipes.map((recipe) => recipe.name)).toEqual([
            'Pasta Carbonara',
            ['Pasta Carbonara', 'Italian pasta'],
            ['Chocolate Cake', 'Brownie'],
            'Salad',
            ['Pizza', 'Calzone', 'Pizza Calzone'],
            'Pizza',
            ['Pizza', 'Pizza Carcassonne'],
            ['Spaghetti Bolognese', 'Garlic Bread'],
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
            ['Pasta Carbonara', 'Italian pasta'],
            ['Chocolate Cake', 'Brownie'],
            'Salad',
            ['Pizza', 'Calzone', 'Pizza Calzone'],
            'Pizza',
            ['Pizza', 'Pizza Carcassonne'],
            ['Spaghetti Bolognese', 'Garlic Bread'],
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
            ['Pasta Carbonara', 'Italian pasta'],
            ['Chocolate Cake', 'Brownie'],
            'Salad',
            ['Pizza', 'Calzone', 'Pizza Calzone'],
            'Pizza',
            ['Pizza', 'Pizza Carcassonne'],
            ['Spaghetti Bolognese', 'Garlic Bread'],
        ]);
    });

    /**
     * Test case: it should filter recipes when name property is an array using AND operator.
     */
    test('it should filter recipes when name property is an array using AND operator', () => {
        const filter = new RecipeNamesFilter(
            ['spaghetti bolognese', 'garlic bread'],
            new AndOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(1);
        expect(filteredRecipes[0].name).toEqual([
            'Spaghetti Bolognese',
            'Garlic Bread',
        ]);
    });

    /**
     * Test case: it should filter recipes when name property is an array using OR operator.
     */
    test('it should filter recipes when name property is an array using OR operator', () => {
        const filter = new RecipeNamesFilter(
            ['salad', 'calzone'],
            new OrOperator(),
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(2);
        expect(filteredRecipes.map((recipe) => recipe.name)).toStrictEqual([
            'Salad',
            ['Pizza', 'Calzone', 'Pizza Calzone'],
        ]);
    });

    /**
     * Test case: it should filter recipes if part of name is included using AND operator.
     */
    test('it should filter recipes if part of name is included using AND operator', () => {
        const filter = new RecipeNamesFilter(
            'pasta',
            new AndOperator(),
            'matchSubstring',
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(2);
        expect(filteredRecipes[0].name).toBe('Pasta Carbonara');
        expect(filteredRecipes[1].name).toStrictEqual([
            'Pasta Carbonara',
            'Italian pasta',
        ]);
    });

    /**
     * Test case: it should filter recipes if part of name is included using OR operator.
     */
    test('it should filter recipes if part of name is included using OR operator', () => {
        const filter = new RecipeNamesFilter(
            'pasta',
            new OrOperator(),
            'matchSubstring',
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(2);
        expect(filteredRecipes[0].name).toBe('Pasta Carbonara');
        expect(filteredRecipes[1].name).toStrictEqual([
            'Pasta Carbonara',
            'Italian pasta',
        ]);
    });

    /**
     * Test case: it should filter recipes with fuzzy search using AND operator.
     */
    test('it should filter recipes with fuzzy search using AND operator', () => {
        const filter = new RecipeNamesFilter(
            'Piza Car',
            new AndOperator(),
            'fuzzy',
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(5);
        expect(filteredRecipes[0].name).toBe('Pasta Carbonara');
        expect(filteredRecipes[1].name).toStrictEqual([
            'Pasta Carbonara',
            'Italian pasta',
        ]);
        expect(filteredRecipes[2].name).toStrictEqual([
            'Pizza',
            'Calzone',
            'Pizza Calzone',
        ]);
        expect(filteredRecipes[3].name).toBe('Pizza');
        expect(filteredRecipes[4].name).toStrictEqual([
            'Pizza',
            'Pizza Carcassonne',
        ]);
    });

    /**
     * Test case: it should filter recipes with fuzzy search using OR operator.
     */
    test('it should filter recipes with fuzzy search using OR operator', () => {
        const filter = new RecipeNamesFilter(
            'Piza Car',
            new OrOperator(),
            'fuzzy',
        );
        const filteredRecipes = recipes.filter((recipe) =>
            filter.filter(recipe),
        );
        expect(filteredRecipes).toHaveLength(5);
        expect(filteredRecipes[0].name).toBe('Pasta Carbonara');
        expect(filteredRecipes[1].name).toStrictEqual([
            'Pasta Carbonara',
            'Italian pasta',
        ]);
        expect(filteredRecipes[2].name).toStrictEqual([
            'Pizza',
            'Calzone',
            'Pizza Calzone',
        ]);
        expect(filteredRecipes[3].name).toBe('Pizza');
        expect(filteredRecipes[4].name).toStrictEqual([
            'Pizza',
            'Pizza Carcassonne',
        ]);
    });
});
