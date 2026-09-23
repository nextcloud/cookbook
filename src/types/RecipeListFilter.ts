// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

export interface FilterEntries {
	operator: 'and' | 'or';
	entries: string[];
}

export interface Filter {
	categories: FilterEntries;
	keywords: FilterEntries;
	searchTerm: string;
}
