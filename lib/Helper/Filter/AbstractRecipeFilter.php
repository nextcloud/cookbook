<?php

namespace OCA\Cookbook\Helper\Filter;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCP\Files\File;

/**
 * An abstract filter on a recipe.
 * 
 * A filter should have a single purpose that is serves and implement this interface
 */
interface AbstractRecipeFilter {
	/**
	 * Filter the given recipe according to the filter class specification.
	 * 
	 * This function can make changes to the recipe array to carry out the needed changes.
	 * In order to signal if the JSON file needs updating, the return value must be true if and only if the recipe was changed.
	 *
	 * @param array $json The recipe data as array
	 * @param File $recipe The file containing the recipe for further processing
	 * @return boolean true, if and only if the recipe was changed
	 * @throws InvalidRecipeException if the recipe was not correctly filtered
	 */
	public function apply(array &$json, File $recipe): bool;
}
