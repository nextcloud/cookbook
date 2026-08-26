<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper;

/**
 * This class handles the encoding of downloads in order to only work with UTF8 strings.
 */
class DownloadEncodingHelper {
	/**
	 * Encode a string to UTF8
	 *
	 * @param string $data The data to be converted
	 * @param string $encoding The encoding of the input string
	 * @return string The string encoded in UTF8 encoding
	 */
	public function encodeToUTF8(string $data, string $encoding): string {
		$dataConverted = iconv($encoding, 'UTF-8', $data);
		return ($dataConverted === false) ? $data : $dataConverted;
	}
}
