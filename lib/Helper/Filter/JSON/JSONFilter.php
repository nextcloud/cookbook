<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

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
		FixTimestampsFilter $fixTimestampsFilter,
		FixToolsFilter $fixToolsFilter,
		FixIngredientsFilter $fixIngredientsFilter,
		FixInstructionsFilter $fixInstructionsFilter,
		FixDescriptionFilter $fixDescriptionFilter,
		FixUrlFilter $fixUrlFilter,
		FixDurationsFilter $fixDurationsFilter,
		FixNutritionFilter $fixNutritionFilter,
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
			$fixTimestampsFilter,
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
