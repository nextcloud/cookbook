<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\UserFolderNotValidException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Exception\UserNotLoggedInException;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IL10N;

/**
 * This class caches the access to the user folder throughout the app.
 *
 * The user folder is the path, were all recipes are stored.
 */
class UserFolderHelper {
	/**
	 * @var UserConfigHelper
	 */
	private $config;

	/**
	 * @var IL10N
	 */
	private $l;

	/**
	 * @var ?string
	 */
	private $userId;

	/**
	 * @var IRootFolder
	 */
	private $root;

	/**
	 * The folder with all recipes or null if this is not yet cached
	 *
	 * @var ?Node
	 */
	private $cache;

	public function __construct(
		?string $UserId,
		IRootFolder $root,
		IL10N $l,
		UserConfigHelper $configHelper
	) {
		$this->userId = $UserId;
		$this->root = $root;
		$this->l = $l;
		$this->config = $configHelper;

		$this->cache = null;
	}

	/**
	 * Set the current path in the settings relative to the user's root folder
	 *
	 * @param string $path The name of the path to be used for the recipes
	 */
	public function setPath(string $path) {
		$this->config->setFolderName($path);
		$this->cache = null;
	}

	/**
	 * Get the path of the recipes relative to the user's root folder
	 *
	 * @return string The relative path name
	 * @throws UserNotLoggedInException If there is currently no logged in user
	 */
	public function getPath(): string {
		$path = $this->config->getFolderName();

		// TODO This was in the original code. Is it still needed?
		// $path = str_replace('//', '/', $path);

		return $path;
	}

	/**
	 * Get the current folder where all recipes are stored of the user
	 *
	 * @return Folder The folder containing all recipes
	 * @throws UserFolderNotValidException If the saved user folder is a file or could not be generated
	 * @throws UserNotLoggedInException If there is no logged-in user at that time
	 */
	public function getFolder(): Folder {
		// Ensure the cache is built.
		if (is_null($this->cache)) {
			$path = $this->getPath();

			// Correct path to be relative to nc root
			$path = '/' . $this->userId . '/files/' . $path;
			$path = str_replace('//', '/', $path);


			$this->cache = $this->getOrCreateFolder($path);
		}

		return $this->cache;
	}

	private function getOrCreateFolder($path): Folder {
		try {
			$node = $this->root->get($path);
		} catch (NotFoundException $ex) {
			try {
				$node = $this->root->newFolder($path);
			} catch (NotPermittedException $ex1) {
				throw new UserFolderNotValidException(
					$this->l->t('The user folder cannot be created due to missing permissions.'),
					0,
					$ex1
				);
			}
		}

		if ($node->getType() !== FileInfo::TYPE_FOLDER) {
			throw new UserFolderNotValidException(
				$this->l->t('The configured user folder is a file.')
			);
		}

		if (! $node->isCreatable()) {
			throw new UserFolderNotWritableException(
				$this->l->t('User cannot create recipe folder')
			);
		}

		return $node;
	}
}
