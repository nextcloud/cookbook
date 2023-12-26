import RecipeCategoryFilter from '../../../js/RecipeFilters/RecipeCategoryFilter';
import applyFilters from '../../../js/utils/applyRecipeFilters';

describe('RecipeCategoryFilter', () => {
    test('it should filter recipes by category', () => {
        const filter = new RecipeCategoryFilter('main');
        const recipes = [
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta'],
                category: 'main',
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: 'main',
            },
            {
                name: 'Chocolate Cake',
                keywords: ['chocolate'],
                category: 'dessert',
            },
        ];

        const result = applyFilters(recipes, [filter]);

        expect(result).toEqual([
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta'],
                category: 'main',
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: 'main',
            },
        ]);
    });

    test('it should filter recipes with array category property by category', () => {
        const filter = new RecipeCategoryFilter('main');
        const recipes = [
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta'],
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: 'main',
            },
            {
                name: 'Chocolate Cake',
                keywords: ['chocolate'],
                category: ['dessert'],
            },
        ];

        const result = applyFilters(recipes, [filter]);

        expect(result).toEqual([
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta'],
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: 'main',
            },
        ]);
    });

    test('it should handle case-insensitive filtering', () => {
        const filter = new RecipeCategoryFilter('MAIN');
        const recipes = [
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta'],
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['Main'],
            },
            {
                name: 'Chocolate Cake',
                keywords: ['chocolate'],
                category: ['dessert'],
            },
        ];

        const result = applyFilters(recipes, [filter]);

        expect(result).toEqual([
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta'],
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['Main'],
            },
        ]);
    });

    test('it should handle missing category property', () => {
        const filter = new RecipeCategoryFilter('main');
        const recipes = [
            { name: 'Spaghetti Bolognese', keywords: ['pasta'] },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['Main'],
            },
            {
                name: 'Chocolate Cake',
                keywords: ['chocolate'],
                category: ['dessert'],
            },
        ];

        const result = applyFilters(recipes, [filter]);

        expect(result).toEqual([
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['Main'],
            },
        ]);
    });
});
