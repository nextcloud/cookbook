// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import type { RecipeNutrition } from './RecipeNutrition';
import type { RecipeTimer } from './RecipeTimer';

export interface Recipe {
	id?: number | string;
	name?: string;
	description?: string;
	url?: string;
	image?: string;
	prepTime?: string;
	cookTime?: string;
	totalTime?: string;
	recipeCategory?: string;
	category?: string;
	keywords?: string | string[];
	recipeYield?: string | number | null;
	tool?: string[];
	recipeIngredient?: string[];
	recipeInstructions?: string[];
	ingredients?: string[];
	instructions?: string[];
	tools?: string[];
	nutrition: RecipeNutrition;
	timerCook?: RecipeTimer | null;
	timerPrep?: RecipeTimer | null;
	timerTotal?: RecipeTimer | null;
	dateCreated?: string | null;
	dateModified?: string | null;
	[key: string]: unknown;
}
