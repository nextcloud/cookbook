<?php

namespace OCA\Cookbook\Helper\Filter\DB;

use OCP\Files\File;

class NormalizeRecipeFileFilter {
	/** @var array */
	private $filters;

	public function __construct(
		RecipeDatesFilter $datesFilter
	) {
		$this->filters = [
			$datesFilter,
		];
	}

	/**
	 * Normalize a recipe file according to the installed filters
	 *
	 * If the recipe needs updating, the file gets overwritten in the storage.
	 *
	 * @param array $json The content of the recipe
	 * @param File $recipeFile The file containing the recipe
	 * @param bool $updateFiles true, if the file on storage should be updated with the modified version
	 * @return array The corrected recipe object
	 */
	public function filter(array $json, File $recipeFile, bool $updateFiles = false): array {
		$changed = false;

		foreach ($this->filters as $filter) {
			/** @var AbstractRecipeFilter $filter */
			$ret = $filter->apply($json, $recipeFile);
			$changed = $changed || $ret;
		}

		if ($changed && $updateFiles) {
			$recipeFile->putContent(json_encode($json));
			$recipeFile->touch();
		}

		return $json;
	}
}
