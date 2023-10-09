<?php

namespace OCA\Cookbook\Helper\Filter;

use OCA\Cookbook\Helper\Filter\JSON\CleanCategoryFilter;
use OCA\Cookbook\Helper\Filter\JSON\ExtractImageUrlFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixDescriptionFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixDurationsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixImageSchemeFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixIngredientsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixInstructionsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixKeywordsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixNutritionFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixRecipeYieldFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixToolsFilter;
use OCA\Cookbook\Helper\Filter\JSON\FixUrlFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdCopyFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdTypeFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeNameFilter;
use OCA\Cookbook\Helper\Filter\JSON\SchemaConformityFilter;

class RecipeStubFilter
{
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

	public function apply(array $json): array
	{
		foreach ($this->filters as $filter) {
			$filter->apply($json);
		}

		return $json;
	}
}
