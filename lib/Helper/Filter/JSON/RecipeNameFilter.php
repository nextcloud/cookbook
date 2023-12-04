<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Helper\TextCleanupHelper;

/**
 * Fix the name of a recipe.
 *
 * This has the effect that any bad characters are prevented in the name using the text cleanup class.
 * Additionally, all slashes and newlines are removed and the name is trimmed to be at most as long as the DB can store.
 */
class RecipeNameFilter extends AbstractJSONFilter {
	private const MAX_LEN = 256;

	/** @var TextCleanupHelper */
	private $textCleanupHelper;

	public function __construct(TextCleanupHelper $textCleanupHelper) {
		$this->textCleanupHelper = $textCleanupHelper;
	}

	public function apply(array &$json): bool {
		// Clean up name to prevent issues
		$cleanedName = $this->textCleanupHelper->cleanUp($json['name'], true, true);
		$changed = ($json['name'] !== $cleanedName);

		// Restrict length of name
		if (strlen($cleanedName) > self::MAX_LEN) {
			$cleanedName = substr($cleanedName, 0, self::MAX_LEN - 1) . '…';
			$changed = true;
		}

		$json['name'] = $cleanedName;

		return $changed;
	}
}
