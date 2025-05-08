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
        if (array_key_exists('id', $json)) {
            $json['id'] = strval($json['id']);
        }
        // Check and fix `id` under `author`
        if (array_key_exists('author', $json) && is_array($json['author']) && array_key_exists('id', $json['author'])) {
            $json['id'] = strval($json['author']['id']);
        }

		return $json !== $copy;
	}
}
