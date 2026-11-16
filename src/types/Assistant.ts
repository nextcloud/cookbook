// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

import type { RecipeNutrition } from './RecipeNutrition';

/**
 * The fields of a recipe that the Nextcloud Assistant may change.
 * Only the fields returned by the assistant are present.
 */
export interface AssistantRecipeUpdate {
	name?: string;
	description?: string;
	recipeCategory?: string;
	keywords?: string;
	prepTime?: string | null;
	cookTime?: string | null;
	totalTime?: string | null;
	recipeYield?: number | null;
	tool?: string[];
	recipeIngredient?: string[];
	recipeInstructions?: string[];
	nutrition?: RecipeNutrition;
}

export type AssistantTaskStatus =
	'scheduled' | 'running' | 'successful' | 'failed' | 'cancelled';

export interface AssistantTaskState {
	status: AssistantTaskStatus;
	recipe?: AssistantRecipeUpdate;
	error?: string;
}
