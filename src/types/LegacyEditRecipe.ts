// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import type { Recipe } from './Recipe';
import type { RecipeNutrition } from './RecipeNutrition';

export interface LegacyEditRecipe extends Recipe {
	id: number;
	name: string;
	description: string;
	url: string;
	image: string;
	prepTime: string;
	cookTime: string;
	totalTime: string;
	recipeCategory: string;
	keywords: string;
	recipeYield: string | null;
	tool: string[];
	recipeIngredient: string[];
	recipeInstructions: string[];
	nutrition: RecipeNutrition;
}
