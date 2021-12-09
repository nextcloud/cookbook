<?php

namespace OCA\Cookbook\Entity;

use OCA\Cookbook\Helper\UserFolderHelper;
use OCP\Files\Folder;

/**
 * This class represents a single cookbook.
 *
 * A cookbook is a set of recipes.
 * At a later time, there might as well be sub-cookbooks as well.
 *
 * The class represents the folder holding all the relevant recipes in the storage.
 * For further optimization, the class provides a caching routine in order to obtain teh recipes quickly:
 * All recipe folders are cached by their name so that directory traversal can be avoided if possible.
 */
class Cookbook {

	/**
	 * @var UserFolderHelper
	 */
	private $userFolderHelper;

	/** @var array */
	private $recipeFolderCache;

	public function __construct(UserFolderHelper $userFolderHelper) {
		$this->userFolderHelper = $userFolderHelper;

		$this->recipeFolderCache = [];
	}

	/**
	 * Get the folder of the cookbook.
	 *
	 * @return Folder The folder which holds all (child) recipes of the cookbook
	 */
	public function getFolder(): Folder {
		return $this->userFolderHelper->getFolder();
	}

	/**
	 * Get the folder for a given recipe from the cache.
	 *
	 * The recipe folder might or might not yet have been requested.
	 * If it was not yet requested/registered, it will not be in the cache.
	 * This does not mean that the recipe folder is non-exsiting.
	 * Instead a direct directory access on the storage should be carried out.
	 *
	 * @param string $folderName The name of the recipe to look for
	 * @return Folder|null The folder of the recipe or null if the named recipe
	 * 					   was not found in the cache
	 */
	public function getRecipeFolder(string $folderName): ?Folder {
		if (array_key_exists($folderName, $this->recipeFolderCache)) {
			return $this->recipeFolderCache[$folderName];
		}
		return null;
	}

	/**
	 * Add a folder to the cache
	 *
	 * This method allows to add entries to the cache.
	 * Once a folder was queried from the storage system, it should be registered with the cookbook's cache.
	 *
	 * @param string $folderName The name of the folder of the recipe
	 * @param Folder $folder The folder object to register
	 * @return void
	 */
	public function cacheRecipeFolder(string $folderName, Folder $folder): void {
		$this->recipeFolderCache[$folderName] = $folder;
	}
}
