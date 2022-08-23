<?php

namespace OCA\Cookbook\Helper\Filter;

use OCA\Cookbook\Helper\Filter\Output\EnsureNutritionPresentFilter;
use OCP\Files\File;

class RecipeJSONOutputFilter {
	/** @var array */
	private $filters;

	public function __construct(
		EnsureNutritionPresentFilter $ensureNutritionPresentFilter
	) {
		$this->filters = [
			$ensureNutritionPresentFilter,
		];
	}

	/**
	 * Fix the JSON output of a file to match the specifications
	 *
	 * @param array $json The content of the recipe
	 * @return array The corrected recipe object
	 */
	public function filter(array $json): array {
		foreach ($this->filters as $filter) {
			/** @var AbstractJSONFilter $filter */
			$filter->apply($json);
		}

		return $json;
	}
}
