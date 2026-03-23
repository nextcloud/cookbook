<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Helper\FolderHelper;
use OCA\Cookbook\Exception\UserNotLoggedInException;

/**
 * This class caches the access to the user's recipe folder throughout the app.
 *
 * The user's recipes folder is the path, were all recipes are stored.
 */
class MyRecipesFolderHelper extends FolderHelper {
	/**
	 * Set the current path in the settings relative to the user's root folder
	 *
	 * @param string $path The name of the path to be used for the recipes
	 */
	#[\Override]
	public function setPath(string $path) {
		$this->config->setMyRecipesFolderName($path);
		$this->cache = null;
	}

	/**
	 * Get the path of the user's recipes relative to the user's root folder
	 *
	 * @return string The relative path name
	 * @throws UserNotLoggedInException If there is currently no logged in user
	 */
	#[\Override]
	public function getPath(): string {
		$path = $this->config->getMyRecipesFolderName();

		// TODO This was in the original code. Is it still needed?
		// $path = str_replace('//', '/', $path);

		return $path;
	}
}
