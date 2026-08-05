// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

/**
 * Function to apply a set of filters on an array of recipes.
 * @param {Object[]} recipes - The array of recipe objects to be filtered.
 * @param {RecipeFilter[]} filters - The array of filters to apply.
 * @returns {Object[]} The filtered array of recipes.
 */
function applyRecipeFilters(recipes, filters) {
    return recipes.filter((recipe) =>
        filters.every((filter) => filter.filter(recipe)),
    );
}

export default applyRecipeFilters;
