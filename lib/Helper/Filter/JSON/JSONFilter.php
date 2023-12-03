<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

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
