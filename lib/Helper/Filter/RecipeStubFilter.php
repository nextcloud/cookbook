<?php

namespace OCA\Cookbook\Helper\Filter;

use OCA\Cookbook\Helper\Filter\JSON\RecipeIdCopyFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdTypeFilter;

class RecipeStubFilter {
	/** @var AbstractJSONFilter[] */
	private $filters;

	public function __construct(
		RecipeIdTypeFilter $recipeIdTypeFilter,
		RecipeIdCopyFilter $recipeIdCopyFilter,
	) {
		$this->filters = [
			$recipeIdCopyFilter,
			$recipeIdTypeFilter,
		];
	}

	public function apply(array $json): array {
		foreach ($this->filters as $filter) {
			$filter->apply($json);
		}

		return $json;
	}
}
