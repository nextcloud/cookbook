<?php

namespace OCA\Cookbook\Helper;

use NotImplementedException;
use OCA\Cookbook\Exception\FolderNotValidException;
use OCA\Cookbook\Exception\FolderNotWritableException;
use OCA\Cookbook\Exception\UserNotLoggedInException;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IL10N;

/**
 * This class caches the access to a folder throughout the app.
 *
 */
class FolderHelper {
	/**
	 * @var UserConfigHelper
	 */
	protected $config;

	/**
	 * @var IL10N
	 */
	protected $l;

	/**
	 * @var ?string
	 */
	protected $userId;

	/**
	 * @var IRootFolder
	 */
	protected $root;

	/**
	 * The folder or null if this is not yet cached
	 *
	 * @var ?Node
	 */
	protected $cache;

	public function __construct(
		?string $UserId,
		IRootFolder $root,
		IL10N $l,
		UserConfigHelper $configHelper,
	) {
		$this->userId = $UserId;
		$this->root = $root;
		$this->l = $l;
		$this->config = $configHelper;

		$this->cache = null;
	}

	/**
	 * Set the path relative to the user's root folder
	 *
	 * @param string The relative path name
	 * @throws NotImplementedException If the function is not implemented in the child class
	 */
	#[\Override]
	public function setPath(string $path) {
		throw new NotImplementedException();
	}

	/**
	 * Get the path relative to the user's root folder
	 *
	 * @return string The relative path name
	 * @throws NotImplementedException If the function is not implemented in the child class
	 */
	#[\Override]
	public function getPath(): string {
		throw new NotImplementedException();
	}

	/**
	 * Get the current folder
	 *
	 * @return Folder The folder
	 * @throws FolderNotValidException If the folder is a file or could not be generated
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

	/**
	 * Get or Create the folder at path
	 *
	 * @param string The folder's relative path name
	 * @return Folder The folder at path
	 * @throws FolderNotValidException If the folder is a file or could not be generated
	 * @throws FolderNotWritableException If the folder cannot be written
	 */
	protected function getOrCreateFolder(string $path): Folder {
		try {
			$node = $this->root->get($path);
		} catch (NotFoundException $ex) {
			try {
				$node = $this->root->newFolder($path);
			} catch (NotPermittedException $ex1) {
				throw new FolderNotValidException(
					$this->l->t('The folder cannot be created due to missing permissions.'),
					0,
					$ex1
				);
			}
		}

		if ($node->getType() !== FileInfo::TYPE_FOLDER) {
			throw new FolderNotValidException(
				$this->l->t('The configured folder is a file.')
			);
		}

		if (!$node->isCreatable()) {
			throw new FolderNotWritableException(
				$this->l->t('User cannot create folder')
			);
		}

		return $node;
	}
}
