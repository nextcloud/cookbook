<?php

namespace OCA\Cookbook\Helper;

use OCP\IConfig;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\IL10N;

/**
 * This class caches the access to the user folder throughout the app.
 *
 * The user folder is the path, were all recipes are stored.
 */
class UserFolderHelper {

	/**
	 * @var IConfig
	 */
	private $config;

	/**
	 * @var IL10N
	 */
	private $l;

	/**
	 * @var string
	 */
	private $userId;

	/**
	 * @var FilesystemHelper
	 */
	private $filesystem;

	/**
	 * The folder with all recipes or null if this is not yet cached
	 *
	 * @var ?Node
	 */
	private $cache;

	public function __construct(
		string $user_id,
		IConfig $config,
		IL10N $l,
		FilesystemHelper $filesystem
	) {
		$this->config = $config;
		$this->l = $l;
		$this->userId = $user_id;
		$this->filesystem = $filesystem;

		$this->cache = null;
	}
	
	/**
	 * Set the current path in the settings relative to the user's root folder
	 *
	 * @param string $path The name of the path to be used for the recipes
	 * @return void
	 */
	public function setPath(string $path) {
		$this->config->setUserValue($this->userId, 'cookbook', 'folder', $path);
		$this->cache = null;
	}

	/**
	 * Get the path of the recipes relative to the user's root folder
	 *
	 * @return string The relative path name
	 */
	public function getPath(): string {
		$path = $this->config->getUserValue($this->userId, 'cookbook', 'folder');
		if ($path === false) {
			$path = '/' . $this->l->t('Recipes');
			$this->config->setUserValue($this->userId, 'cookbook', 'folder', $path);
		}

		return $path;
	}

	/**
	 * Get the current folder where all recipes are stored of the user
	 *
	 * @return Folder The folder containing all recipes
	 */
	public function getFolder(): Folder {
		// Ensure the cache is built.
		if (is_null($this->cache)) {
			$path = $this->getPath();

			// Correct path to be realtice to nc root
			$path = '/' . $this->userId . '/files/' . $path;
			$path = str_replace('//', '/', $path);
			
			$this->cache = $this->filesystem->ensureFolderExists($path);
		}

		return $this->cache;
	}
}
