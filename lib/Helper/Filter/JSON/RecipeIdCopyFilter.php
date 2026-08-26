<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\Filter\JSON;

/**
 * Copy the id from the recipe_id to the id field if there is no id present so far.
 */
class RecipeIdCopyFilter extends AbstractJSONFilter {
	#[\Override]
	public function apply(array &$json): bool {
		$copy = $json;
		if (!isset($json['id'])) {
			$json['id'] = $json['recipe_id'];
		}

		return $json !== $copy;
	}
}
