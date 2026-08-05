<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\Filter\Output;

use OCA\Cookbook\Helper\Filter\JSON\AbstractJSONFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdCopyFilter;
use OCA\Cookbook\Helper\Filter\JSON\RecipeIdTypeFilter;
use OCA\Cookbook\Helper\Filter\JSON\TimestampFixFilter;
use OCA\Cookbook\Helper\Filter\JSON\TimezoneFixFilter;

class RecipeStubFilter {
	/** @var AbstractJSONFilter[] */
	private $filters;

	public function __construct(
		RecipeIdTypeFilter $recipeIdTypeFilter,
		RecipeIdCopyFilter $recipeIdCopyFilter,
		TimestampFixFilter $timestampFixFilter,
		TimezoneFixFilter $timezoneFixFilter,
	) {
		$this->filters = [
			$recipeIdCopyFilter,
			$recipeIdTypeFilter,
			$timestampFixFilter,
			$timezoneFixFilter,
		];
	}

	public function apply(array $json): array {
		foreach ($this->filters as $filter) {
			$filter->apply($json);
		}

		return $json;
	}
}
