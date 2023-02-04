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
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdTypeFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeNameFilter;
use OCA\Cookbook\Helper\Filter\JSON\SchemaConformityFilter;

class JSONFilter {
	/** @var AbstractJSONFilter[] */
	private $filters;

	public function __construct(
		SchemaConformityFilter $schemaConformityFilter,
		RecipeNameFilter $recipeNameFilter,
		RecipeIdTypeFilter $recipeIdTypeFilter,
		ExtractImageUrlFilter $extractImageUrlFilter,
		FixImageSchemeFilter $fixImageSchemeFilter,
		CleanCategoryFilter $cleanCategoryFilter,
		FixRecipeYieldFilter $fixRecipeYieldFilter,
		FixKeywordsFilter $fixKeywordsFilter,
		FixToolsFilter $fixToolsFilter,
		FixIngredientsFilter $fixIngredientsFilter,
		FixInstructionsFilter $fixInstructionsFilter,
		FixDescriptionFilter $fixDescriptionFilter,
		FixUrlFilter $fixUrlFilter,
		FixDurationsFilter $fixDurationsFilter,
		FixNutritionFilter $fixNutritionFilter
	) {
		$this->filters = [
			$schemaConformityFilter,
			$recipeNameFilter,
			$recipeIdTypeFilter,
			$extractImageUrlFilter,
			$fixImageSchemeFilter,
			$cleanCategoryFilter,
			$fixRecipeYieldFilter,
			$fixKeywordsFilter,
			$fixToolsFilter,
			$fixIngredientsFilter,
			$fixInstructionsFilter,
			$fixDescriptionFilter,
			$fixUrlFilter,
			$fixDurationsFilter,
			$fixNutritionFilter
		];
	}

	public function apply(array $json): array {
		foreach ($this->filters as $filter) {
			$filter->apply($json);
		}

		return $json;
	}
}
