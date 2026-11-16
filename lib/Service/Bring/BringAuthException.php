<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service\Bring;

/**
 * Bring! rejected the credentials or the token.
 *
 * The user has to connect to Bring! again.
 */
class BringAuthException extends \RuntimeException {
}
