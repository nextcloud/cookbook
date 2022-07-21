<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;

/**
 * Clean the category of a recipe.
 *
 * A recipe must have at most one category.
 * This category must be a string.
 * Recipes without category are assigned the empty string for the category.
 */
class CleanCategoryFilter extends AbstractJSONFilter {
	/** @var TextCleanupHelper */
	private $textCleaner;

	public function __construct(TextCleanupHelper $cleanupHelper) {
		$this->textCleaner = $cleanupHelper;
	}

	public function apply(array &$json): bool {
		if (! isset($json['recipeCategory'])) {
			$json['recipeCategory'] = '';
			return true;
		}

		$cache = $json['recipeCategory'];

		if (is_array($json['recipeCategory'])) {
			reset($json['recipeCategory']);
			$json['recipeCategory'] = current($json['recipeCategory']);
		}

		if (!is_string($json['recipeCategory'])) {
			$json['recipeCategory'] = '';
		}

		$json['recipeCategory'] = $this->textCleaner->cleanUp($json['recipeCategory'], true, true);

		return $cache !== $json['recipeCategory'];
	}
}
