<?php

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
