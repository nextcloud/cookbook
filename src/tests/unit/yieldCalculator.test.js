// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import yieldCalculator from '../../js/yieldCalculator';

const {
    isValidIngredientSyntax,
    isIngredientsArrayValid,
    recalculateIngredients,
} = yieldCalculator;

describe('isValidIngredientSyntax', () => {
    test.each([
        '¾ cup milk',
        '½ tsp salt',
        '1 ½ cups flour',
        '1/2 cup sugar',
        '1 1/2 cups flour',
        '200g carrots',
        '200 g carrots',
        '1.5 l water',
        '## Dough',
    ])('accepts "%s"', (ingredient) => {
        expect(isValidIngredientSyntax(ingredient)).toBe(true);
    });

    test.each(['salt to taste', '1.2.3 g sugar', '200g', '¾'])(
        'rejects "%s"',
        (ingredient) => {
            expect(isValidIngredientSyntax(ingredient)).toBe(false);
        },
    );
});

describe('isIngredientsArrayValid', () => {
    test('accepts ingredients with unicode fractions', () => {
        expect(
            isIngredientsArrayValid(['## Dough', '¾ cup milk', '200g flour']),
        ).toBe(true);
    });

    test('rejects if one ingredient is invalid', () => {
        expect(isIngredientsArrayValid(['¾ cup milk', 'salt to taste'])).toBe(
            false,
        );
    });
});

describe('recalculateIngredients', () => {
    test.each([
        ['¾ cup milk', 1, 2, '1 1/2 cup milk'],
        ['1/2 cup sugar', 1, 3, '1 1/2 cup sugar'],
        ['1 1/2 cups flour', 2, 1, '3/4 cups flour'],
        ['1/3 cup oil', 1, 1, '0.33 cup oil'],
        ['200g carrots', 2, 3, '300g carrots'],
        ['1.5 l water', 1, 2, '3 l water'],
        ['## Dough', 1, 2, '## Dough'],
        ['salt to taste', 1, 2, 'salt to taste'],
    ])(
        'scales "%s" from %i to %i servings',
        (ingredient, originalYield, currentYield, expected) => {
            expect(
                recalculateIngredients(
                    [ingredient],
                    currentYield,
                    originalYield,
                ),
            ).toEqual([expected]);
        },
    );

    test.each([0, 1.5])(
        'keeps ingredients unchanged for original yield %p',
        (originalYield) => {
            expect(
                recalculateIngredients(['200g carrots'], 2, originalYield),
            ).toEqual(['200g carrots']);
        },
    );
});
