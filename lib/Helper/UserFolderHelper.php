<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\UserFolderNotValidException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Exception\WrongFileTypeException;
use OCP\IConfig;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\Files\NotPermittedException;
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
		string $UserId,
		IConfig $config,
		IL10N $l,
		FilesystemHelper $filesystem
	) {
		$this->config = $config;
		$this->l = $l;
		$this->userId = $UserId;
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
		$path = $this->config->getUserValue($this->userId, 'cookbook', 'folder', null);
		if ($path === null) {
			$path = '/' . $this->l->t('Recipes');
			$this->config->setUserValue($this->userId, 'cookbook', 'folder', $path);
		}

		return $path;
	}

	/**
	 * Get the current folder where all recipes are stored of the user
	 *
	 * @return Folder The folder containing all recipes
	 * @throws UserFolderNotValidException If the saved user folder is a file or could not be generated
	 * @throws UserFolderNotWritableException The the saved user folder is not writable.
	 */
	public function getFolder(): Folder {
		// Ensure the cache is built.
		if (is_null($this->cache)) {
			$path = $this->getPath();

			// Correct path to be realtice to nc root
			$path = '/' . $this->userId . '/files/' . $path;
			$path = str_replace('//', '/', $path);
			
			try {
				$this->cache = $this->filesystem->ensureFolderExists($path);
			} catch (WrongFileTypeException $ex) {
				throw new UserFolderNotValidException(
					$this->l->t('The configured user folder is a file.'),
					null,
					$ex
				);
			} catch (NotPermittedException $ex) {
				throw new UserFolderNotValidException(
					$this->l->t('The user folder cannot be created due to missing permissions.'),
					null,
					$ex
				);
			}

			if (! $this->filesystem->folderHasFullPermissions($this->cache)) {
				throw new UserFolderNotWritableException(
					$this->l->t('The user folder %s is not writable by the user.', [$this->cache->getPath()])
				);
			}
		}

		return $this->cache;
	}
}
