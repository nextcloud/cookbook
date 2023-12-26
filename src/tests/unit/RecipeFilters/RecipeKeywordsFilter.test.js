import RecipeKeywordsFilter from '../../../js/RecipeFilters/RecipeKeywordsFilter';
import applyFilters from '../../../js/utils/applyRecipeFilters';

describe('RecipeKeywordsFilter', () => {
    test('it should filter recipes by keywords', () => {
        const filter = new RecipeKeywordsFilter(['pasta', 'salty']);
        const recipes = [
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta', 'salty'],
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken', 'salty'],
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
                keywords: ['pasta', 'salty'],
                category: ['main'],
            },
        ]);
    });

    test('it should filter recipes with non-array keyword property', () => {
        const filter = new RecipeKeywordsFilter('salty');
        const recipes = [
            {
                name: 'Spaghetti Bolognese',
                keywords: 'salty',
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken', 'salty'],
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
                keywords: 'salty',
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken', 'salty'],
                category: ['main'],
            },
        ]);
    });

    test('it should handle case-insensitive filtering', () => {
        const filter = new RecipeKeywordsFilter(['Pasta', 'SALTY']);
        const recipes = [
            {
                name: 'Spaghetti Bolognese',
                keywords: ['pasta', 'salty'],
                category: ['main'],
            },
            {
                name: 'Chicken Stir Fry',
                keywords: ['chicken', 'salty'],
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
                keywords: ['pasta', 'salty'],
                category: ['main'],
            },
        ]);
    });

    test('it should handle missing keywords property', () => {
        const filter = new RecipeKeywordsFilter(['chicken']);
        const recipes = [
            { name: 'Spaghetti Bolognese', category: ['main'] },
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
                name: 'Chicken Stir Fry',
                keywords: ['chicken'],
                category: ['main'],
            },
        ]);
    });
});
