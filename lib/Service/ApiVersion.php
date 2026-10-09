<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service;

class ApiVersion {
	public static function getApiVersion(): array {
		return [
			'epoch' => 0,
			'major' => 1,
			'minor' => 2
		];
	}

	public function getAppVersion(): array {
		return [
			0, // x-release-please-major VERSION_TAG
			11, // x-release-please-minor
			12, // x-release-please-patch
		];
	}
}
