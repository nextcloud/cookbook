<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service\Bring;

/**
 * The Bring! API could not be reached or answered unexpectedly.
 *
 * The exception code is the HTTP status, or 0 if there was no response.
 */
class BringApiException extends \RuntimeException {
}
