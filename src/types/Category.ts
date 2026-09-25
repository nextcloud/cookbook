/*
 * SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
 *
 * SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
 */

import type { Recipe } from './Recipe';

export interface Category {
	name: string;
	recipeCount: number;
	recipes: Array<Pick<Recipe, 'id' | 'name'>>;
}

export interface CategoryElement {
	opened: boolean;
	title: string;
}
