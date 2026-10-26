<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Exception;

class ImportException extends \Exception {
	private string $step;

	public function __construct($message = '', $code = 0, $previous = null, string $step = '') {
		parent::__construct($message, $code, $previous);
		$this->step = $step;
	}

	public function getStep(): string {
		return $this->step;
	}
}
