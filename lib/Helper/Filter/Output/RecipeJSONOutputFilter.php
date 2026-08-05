<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\Filter\Output;

use OCA\Cookbook\Helper\Filter\JSON\AbstractJSONFilter;
use OCP\Files\File;

class RecipeJSONOutputFilter {
	/** @var array */
	private $filters;

	public function __construct(
		EnsureNutritionPresentFilter $ensureNutritionPresentFilter,
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
