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
        '1½ cups flour',
        '⅐ tsp pepper',
        '1/2 cup sugar',
        '1 1/2 cups flour',
        '200g carrots',
        '200 g carrots',
        '1.5 l water',
        '## Dough',
    ])('accepts "%s"', (ingredient) => {
        expect(isValidIngredientSyntax(ingredient)).toBe(true);
    });

    test.each([
        'salt to taste',
        '1.2.3 g sugar',
        '200g',
        '¾',
        '² cups flour',
        '① egg',
        '⅟ cup water',
        '1 ¹⁄₂ cup flour',
        '2 ² cups flour',
        '1⁄2 cup sugar',
    ])('rejects "%s"', (ingredient) => {
        expect(isValidIngredientSyntax(ingredient)).toBe(false);
    });
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
        ['1½ cups flour', 1, 3, '4 1/2 cups flour'],
        ['1½ cups flour', 2, 1, '3/4 cups flour'],
        ['1 ¼ cups flour', 1, 2, '2 1/2 cups flour'],
        ['⅛ tsp salt', 1, 2, '1/4 tsp salt'],
        ['11/2 cups flour', 2, 1, '2 3/4 cups flour'],
        ['1/2 cup sugar', 1, 3, '1 1/2 cup sugar'],
        ['1 1/2 cups flour', 2, 1, '3/4 cups flour'],
        ['1/3 cup oil', 1, 1, '0.33 cup oil'],
        ['200g carrots', 2, 3, '300g carrots'],
        ['1.5 l water', 1, 2, '3 l water'],
        ['## Dough', 1, 2, '## Dough'],
        ['salt to taste', 1, 2, 'salt to taste'],
        ['1 ¹⁄₂ cup flour', 1, 2, '1 ¹⁄₂ cup flour'],
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

describe('unicode fractions', () => {
    test.each([
        ['¼', '1/4'],
        ['½', '1/2'],
        ['¾', '3/4'],
        ['⅐', '1/7'],
        ['⅑', '1/9'],
        ['⅒', '1/10'],
        ['⅓', '1/3'],
        ['⅔', '2/3'],
        ['⅕', '1/5'],
        ['⅖', '2/5'],
        ['⅗', '3/5'],
        ['⅘', '4/5'],
        ['⅙', '1/6'],
        ['⅚', '5/6'],
        ['⅛', '1/8'],
        ['⅜', '3/8'],
        ['⅝', '5/8'],
        ['⅞', '7/8'],
    ])('treats "%s" like "%s"', (unicodeFraction, asciiFraction) => {
        [
            `${unicodeFraction} cup milk`,
            `2 ${unicodeFraction} cup milk`,
            `2${unicodeFraction} cup milk`,
        ].forEach((ingredient) => {
            expect(isValidIngredientSyntax(ingredient)).toBe(true);
        });

        [2, 3].forEach((currentYield) => {
            expect(
                recalculateIngredients(
                    [
                        `${unicodeFraction} cup milk`,
                        `2${unicodeFraction} cup milk`,
                    ],
                    currentYield,
                    1,
                ),
            ).toEqual(
                recalculateIngredients(
                    [
                        `${asciiFraction} cup milk`,
                        `2 ${asciiFraction} cup milk`,
                    ],
                    currentYield,
                    1,
                ),
            );
        });
    });
});
