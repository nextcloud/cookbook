<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service\Bring;

/**
 * The data needed to authenticate a single Bring! API call.
 *
 * The access token is short-lived and is never persisted.
 */
class BringAuth {
	public function __construct(
		public string $accessToken,
		public string $uuid,
		public string $publicUuid,
		public string $country,
	) {
	}
}
