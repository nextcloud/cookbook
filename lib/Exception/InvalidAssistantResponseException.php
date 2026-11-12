<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Exception;

/**
 * Thrown if the response of the Nextcloud Assistant could not be interpreted as a recipe.
 */
class InvalidAssistantResponseException extends \Exception {
	public function __construct($message = '', $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
