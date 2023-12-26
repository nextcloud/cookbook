import RecipeNameFilter from '../../../js/RecipeFilters/RecipeNameFilter';
import applyFilters from '../../../js/utils/applyRecipeFilters';

describe('RecipeNameFilter', () => {
    test('it should filter recipes by name', () => {
        const filter = new RecipeNameFilter('Spaghetti');
        const recipes = [
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta'],
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['main'],
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
        ]);
    });

    test('it should handle case-insensitive filtering', () => {
        const filter = new RecipeNameFilter('chicken');
        const recipes = [
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['main'],
            },
            {
                name: 'Roast Chicken',
                keywords: ['chicken'],
                category: ['main'],
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
                category: ['main'],
            },
            {
                name: 'Roast Chicken',
                keywords: ['chicken'],
                category: ['main'],
            },
        ]);
    });

    test('it should handle missing name property', () => {
        const filter = new RecipeNameFilter('Spaghetti');
        const recipes = [
            { keywords: ['pasta'], category: ['main'] },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['main'],
            },
            {
                name: 'Chocolate Cake',
                keywords: ['chocolate'],
                category: ['dessert'],
            },
        ];

        const result = applyFilters(recipes, [filter]);

        expect(result).toEqual([]);
    });
});
