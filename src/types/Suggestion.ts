/*
 * SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
 *
 * SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
 */

export interface SuggestionOption {
	recipe_id: number;
	title: string;
}

export interface SuggestionCaretPosition {
	left: number;
	top: number;
	height: number;
}

export interface SuggestionData {
	field: HTMLElement;
	caretPos: SuggestionCaretPosition;
	searchText?: string;
	focusIndex?: number;
	fieldIndex?: number;
}
