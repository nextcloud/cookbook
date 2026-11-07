<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper;

/**
 * Strips credentials, query and fragment from URLs before they are logged.
 */
class UrlRedactionHelper {
	public static function redact(?string $url): string {
		$parts = parse_url($url ?? '');

		if ($parts === false || !isset($parts['host'])) {
			return '[invalid URL]';
		}

		$scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
		$port = isset($parts['port']) ? ':' . $parts['port'] : '';

		return $scheme . $parts['host'] . $port . ($parts['path'] ?? '');
	}
}
