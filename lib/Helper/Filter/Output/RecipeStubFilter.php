<?php

namespace OCA\Cookbook\Helper\Filter\Output;

use OCA\Cookbook\Helper\Filter\JSON\AbstractJSONFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdCopyFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdTypeFilter;
use OCA\Cookbook\Helper\Filter\JSON\TimestampFixFilter;

class RecipeStubFilter {
	/** @var AbstractJSONFilter[] */
	private $filters;

	public function __construct(
		RecipeIdTypeFilter $recipeIdTypeFilter,
		RecipeIdCopyFilter $recipeIdCopyFilter,
		TimestampFixFilter $timestampFixFilter
	) {
		$this->filters = [
			$recipeIdCopyFilter,
			$recipeIdTypeFilter,
			$timestampFixFilter,
		];
	}

	public function apply(array $json): array {
		foreach ($this->filters as $filter) {
			$filter->apply($json);
		}

		return $json;
	}
}
