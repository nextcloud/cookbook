<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

/**
 * Copy the id from the recipe_id to the id field if there is no id present so far.
 */
class RecipeIdCopyFilter extends AbstractJSONFilter {
	public function apply(array &$json): bool {
		$copy = $json;
		if (! isset($json['id'])) {
			$json['id'] = $json['recipe_id'];
		}
		return $json !== $copy;
	}
}
