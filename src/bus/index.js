// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

/**
 * Nextcloud Cookbook app
 * Event bus module
 * ----------------------
 * @license AGPL3 or later
 */
import mitt from 'mitt';

const emitter = mitt();

export default emitter;
