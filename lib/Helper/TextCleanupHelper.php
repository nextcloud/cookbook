<?php

namespace OCA\Cookbook\Helper;

class TextCleanupHelper {
	/**
	 * Clean up a string as imported from a HTML page.
	 *
	 * @param string|null $str The string to be cleaned up
	 * @param bool $removeNewlines false, if any newlines should be dropped and eventually replaced by blanks
	 * @param bool $removeSlashes true, if any slashed should be removed from the string and possibly replaced by blanks
	 * @return string
	 */
	public function cleanUp(
		?string $str,
		bool $removeNewlines = true,
		bool $removeSlashes = false
	): string {
		if (!$str) {
			return '';
		}

		$str = strip_tags($str);

		if ($removeNewlines) {
			$str = str_replace(["\r", "\n"], ' ', $str);
		}

		$str = str_replace("\t", ' ', $str);
		$str = str_replace("\\", '_', $str);

		// We want to remove forward-slashes for the name of the recipe, to tie it to the directory structure, which cannot have slashes
		if ($removeSlashes) {
			$str = str_replace('/', '_', $str);
		}

		$str = html_entity_decode($str);

		// Remove duplicated spaces
		$str = preg_replace('/  */', ' ', $str);
		$str = trim($str);

		return $str;
	}
}
