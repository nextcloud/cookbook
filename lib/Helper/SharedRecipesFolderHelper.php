<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Helper\FolderHelper;

/**
 * This class caches the access to the shared recipes folder throughout the app.
 *
 * The shared recipes folder is the path, were all recipes are stored.
 */
class SharedRecipesFolderHelper extends FolderHelper {
	/**
	 * Set the current path in the settings relative to the user's root folder
	 *
	 * @param string $path The name of the path to be used for the recipes
	 */
	public function setPath(string $path) {
		$this->config->setSharedRecipesFolderName($path);
		$this->cache = null;
	}

	/**
	 * Get the path of the shared recipes relative to the user's root folder
	 *
	 * @return string The relative path name
	 * @throws UserNotLoggedInException If there is currently no logged in user
	 */
	public function getPath(): string {
		$path = $this->config->getSharedRecipesFolderName();

		// TODO This was in the original code. Is it still needed?
		// $path = str_replace('//', '/', $path);

		return $path;
	}
}
