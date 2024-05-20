<?php

namespace OCA\Cookbook\Helper\Filter\DB;

use OCP\Files\File;

/**
 * Fix the length of a name of a recipe.
 *
 * The name is trimmed to be at most as long as the DB can store.
 */
class RecipeNameLengthFilter implements AbstractRecipeFilter {
	/** @var int */
	private const MAX_LEN = 128;
	/** @var string */
	private const EXTENSION = '…';

	public function __construct() {
	}

	public function apply(array &$json, File $recipe): bool {
		$changed = false;
		$name = trim($json['name']);

		// Restrict length of name
		if (strlen($name) > self::MAX_LEN) {
			$subName = mb_substr($name, 0, self::MAX_LEN - 1);
			$len = strlen($subName . self::EXTENSION);
			while ($len > self::MAX_LEN) {
				$mbLen = mb_strlen($subName, 'UTF-8');
				$subName = trim(mb_substr($subName, 0, $mbLen - 1));
				$len = strlen($subName . self::EXTENSION);
			}

			$name = $subName . self::EXTENSION;
			$changed = true;
		}

		$json['name'] = $name;

		return $changed;
	}
}
