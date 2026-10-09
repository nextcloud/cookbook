// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

/**
 * Parse a schema.org Recipe from JSON text, optionally wrapped in a script tag.
 * @param {string} text - The JSON text.
 * @returns {object|null} The recipe, or null if the JSON is not a schema.org Recipe.
 * @throws {SyntaxError} If the text is not valid JSON.
 */
function parseRecipeJson(text) {
    const json = JSON.parse(
        text
            .trim()
            .replace(/^<script[^>]*>/i, '')
            .replace(/<\/script>$/i, ''),
    );
    const isRecipe =
        json !== null &&
        typeof json === 'object' &&
        !Array.isArray(json) &&
        [json['@type']].flat().includes('Recipe');
    return isRecipe ? json : null;
}

export default parseRecipeJson;
