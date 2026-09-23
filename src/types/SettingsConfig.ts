/*
 * SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
 *
 * SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
 */

export interface SettingsConfig {
	print_image: boolean;
	visibleInfoBlocks: Record<string, boolean>;
	update_interval: number;
	folder: string;
}
