<?php

namespace OCA\Cookbook\Helper\Filter;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCP\Files\File;

/**
 * An abstract filter on a recipe JSON.
 *
 * A filter should have a single purpose that is serves and implement this interface
 */
abstract class AbstractJSONFilter {
	/**
	 * Filter the given recipe according to the filter class specification.
	 *
	 * This function can make changes to the recipe array to carry out the needed changes.
	 * In order to signal if the JSON file needs updating, the return value must be true if and only if the recipe was changed.
	 *
	 * @param array $json The recipe data as array
	 * @return bool true, if and only if the recipe was changed
	 * @throws InvalidRecipeException if the recipe was not correctly filtered
	 */
	abstract public function apply(array &$json): bool;

	protected function setJSONValue(array &$json, string $key, $value): bool {
		if (!array_key_exists($key, $json)) {
			$json[$key] = $value;
			return true;
		} elseif ($json[$key] !== $value) {
			$json[$key] = $value;
			return true;
		}
		return false;
	}
}
