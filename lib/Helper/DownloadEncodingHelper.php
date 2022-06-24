<?php

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
		$data = iconv($encoding, 'UTF-8', $data);
		return $data;
	}
}
