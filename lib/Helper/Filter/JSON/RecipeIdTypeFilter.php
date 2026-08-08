<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\Filter\JSON;

/**
 * Fix the data type of the id of a recipe.
 *
 * The id should be a string and no integer.
 */
class RecipeIdTypeFilter extends AbstractJSONFilter {
	#[\Override]
	public function apply(array &$json): bool {
		$copy = $json;
		if (array_key_exists('id', $json)) {
			$json['id'] = strval($json['id']);
		}

		return $json !== $copy;
	}
}
