/*
 * SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
 *
 * SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later
 */

export interface RequestError {
	response?: {
		status: number;
		data: { msg: string };
	};
	request?: unknown;
}
