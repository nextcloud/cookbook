<?php

namespace OCA\Cookbook\Helper;

/**
 * This class parses the Accepts header of an HTTP request and returns an array of accepted file extensions.
 *
 * The return value is a list of extensions that the client is willing to accept.
 * Higher priorities are sorted first in the array.
 */
class AcceptHeaderParsingHelper {

	/**
	 * Parse the content of a header and generate the list of valid file extensions the client will accept.
	 *
	 * The entries in the return value will be sorted according to the priority given by the sender.
	 * Higher priority entries are sorted first.
	 *
	 * @param string $header The value of the Accept header to be parsed
	 * @return array The sorted list of file extensions that are valid
	 */
	public function parseHeader(string $header): array {
		$parts = explode(',', $header);
		$parts = array_map(function ($x) {
			return trim($x);
		}, $parts);

		// $this->sortParts($parts);
		$weightedParts = $this->sortAndWeightParts($parts);

		$extensions = [];

		foreach ($weightedParts as $wp) {
			$ex = $this->getFileTypes($wp['type']);

			foreach ($ex as $e) {
				if (array_search($e, $extensions) === false) {
					$extensions[] = $e;
				}
			}
		}
		return $extensions;
	}

	/**
	 * Return the list of all supported file extensions by the app.
	 * The return value in the same format as with the parseHeader function
	 *
	 * @return array The list of supported file extensions by the app
	 */
	public function getDefaultExtensions(): array {
		return ['jpg'];
	}

	private function sortAndWeightParts(array $parts): array {
		$weightedParts = array_map(function ($x) {
			return $this->parsePart($x);
		}, $parts);

		usort($weightedParts, function ($a, $b) {
			$tmp = $a['weight'] - $b['weight'];
			if ($tmp < - 0.001) {
				return -1;
			} elseif ($tmp > 0.001) {
				return 1;
			} else {
				return 0;
			}
		});
		$weightedParts = array_reverse($weightedParts);

		return $weightedParts;
	}

	private function parsePart($part): array {
		if (preg_match('/\s*(.+?)\s*;q=([0-9.]+)\s*$/', $part, $matches) === 0) {
			// No qualifier was found
			$mime = trim($part);
			$weight = 1;
		} else {
			// Separate qualifier and part
			$mime = trim($matches[1]);
			$weight = $matches[2];
		}

		return [
			'type' => $mime,
			'weight' => $weight,
		];
	}

	private function getFileTypes(string $mime): array {
		$parts = explode(';', $mime, 2);
		switch ($parts[0]) {
			case 'image/jpeg':
			case 'image/jpg':
				return ['jpg'];
			case 'image/png':
				return ['png'];
			case 'image/svg+xml':
				return ['svg'];
			case 'image/*':
				return ['jpg', 'png', 'svg'];
			case '*/*':
				return ['jpg', 'png', 'svg'];
		}
		return [];
	}
}
