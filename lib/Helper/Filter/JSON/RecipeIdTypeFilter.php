<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

/**
 * Fix the data type of the id of a recipe.
 *
 * The id should be a string and no integer.
 */
class RecipeIdTypeFilter extends AbstractJSONFilter {
	public function apply(array &$json): bool {
		$copy = $json;
		$json['id'] = strval($json['id']);
		return $json !== $copy;
	}
}
