// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import parseRecipeJson from '../../../js/utils/parseRecipeJson';

const recipe = { '@type': 'Recipe', name: 'Bread' };

describe('parseRecipeJson', () => {
    test('recipe object', () => {
        expect(parseRecipeJson(JSON.stringify(recipe))).toEqual(recipe);
    });

    test('recipe wrapped in a script tag', () => {
        const text = `<script type="application/ld+json">\n${JSON.stringify(recipe)}\n</script>\n`;
        expect(parseRecipeJson(text)).toEqual(recipe);
    });

    test('recipe with multiple types', () => {
        const multi = { '@type': ['Recipe', 'NewsArticle'], name: 'Bread' };
        expect(parseRecipeJson(JSON.stringify(multi))).toEqual(multi);
    });

    test('valid JSON that is not a recipe', () => {
        expect(parseRecipeJson('{ "@type": "WebPage" }')).toBeNull();
        expect(parseRecipeJson('{ "name": "Bread" }')).toBeNull();
        expect(parseRecipeJson(JSON.stringify([recipe]))).toBeNull();
        expect(parseRecipeJson('"Recipe"')).toBeNull();
        expect(parseRecipeJson('null')).toBeNull();
    });

    test('invalid JSON', () => {
        expect(() => parseRecipeJson('{ "name": ')).toThrow(SyntaxError);
        expect(() =>
            parseRecipeJson(`Here is your recipe: ${JSON.stringify(recipe)}`),
        ).toThrow(SyntaxError);
    });
});
