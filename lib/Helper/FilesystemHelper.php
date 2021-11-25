<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\WrongFileTypeException;
use OCP\Constants;
use OCP\Files\File;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IL10N;

/**
 * This class abstracts the filesystem of the Nextcloud core for simpler access
 *
 * The main functions  in the core are quite powerfull. This makes it hard to
 * work with them and test efficiently when using them directly.
 *
 * This class should help to abstract some common cases in the app.
 */
class FilesystemHelper {
	private const MASK_READ = Constants::PERMISSION_READ;
	private const MASK_WRITE = Constants::PERMISSION_CREATE | Constants::PERMISSION_DELETE;
	private const MASK_ALL = self::MASK_READ | self::MASK_WRITE;

	/**
	 * @var IRootFolder
	 */
	private $root;

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(IRootFolder $root, IL10N $l) {
		$this->root = $root;
		$this->l = $l;
	}

	/**
	 * Check if a node exists in a directory
	 *
	 * @param string $name The name of the node to look for
	 * @param Folder|null $parent The folder to search in, can be null
	 * (or not given) to look in the root of the user file system.
	 * @return boolean true if there exists a node with the name
	 */
	public function nodeExists(string $name, ?Folder $parent = null): bool {
		if ($parent === null) {
			$parent = $this->root;
		}
		return $parent->nodeExists($name);
	}

	/**
	 * Ensure there exists a folder with a certain name.
	 *
	 * If the folder exists, it is returned.
	 * If it does not exist, it is generated.
	 *
	 * Please note, that the parent folder must exist.
	 *
	 * @param string $name The name of the folder to get or create.
	 * @param Folder|null $parent The folder to search in, can be null
	 * (or not given) to look in the root of the user file system.
	 * @return Folder The generated folder instance
	 * @throws WrongFileTypeException If there exists a file with the givenb name
	 * @throws NotPermittedException If the creation of the folder was not permitted
	 */
	public function ensureFolderExists(string $name, ?Folder $parent = null): Folder {
		$root = $parent ?? $this->root;

		try {
			$node = $root->get($name);
			if ($this->isFolder($node)) {
				return $node;
			} else {
				throw new WrongFileTypeException(
					$this->l->t('Path points to existing file')
				);
			}
		} catch (NotFoundException $ex) {
			try {
				return $root->newFolder($name);
			} catch (NotPermittedException $ex2) {
				throw $ex2;
			}
		}
	}

	/**
	 * Ensure there exists a file with a certain name.
	 *
	 * If the file exists, it is returned.
	 * If it does not exist, it is generated.
	 *
	 * Please note, that the parent folder must exist.
	 *
	 * @param string $name The name of the file to get or create.
	 * @param Folder|null $parent The folder to search in, can be null
	 * (or not given) to look in the root of the user file system.
	 * @return File The generated file instance
	 * @throws WrongFileTypeException If there exists a folder with the givenb name
	 * @throws NotPermittedException If the creation of the file was not permitted
	 */
	public function ensureFileExists(string $name, ?Folder $parent = null): File {
		$root = $parent ?? $this->root;

		try {
			$node = $root->get($name);
			if (! $this->isFolder($node)) {
				return $node;
			} else {
				throw new WrongFileTypeException(
					$this->l->t('Path points to existing folder')
				);
			}
		} catch (NotFoundException $ex) {
			try {
				return $root->newFile($name);
			} catch (NotPermittedException $ex2) {
				throw $ex2;
			}
		}
	}

	/**
	 * Remove a file/folder if is already existing.
	 *
	 * If no node with the given name exists, nothing happens
	 *
	 * @param string $name The file or folder to remove
	 * @param Folder|null $parent The parent to look for if not looking in the root folder of the data
	 * @return void
	 */
	public function ensureNodeDeleted(string $name, ?Folder $parent = null): void {
		$root = $parent ?? $this->root;

		if ($root->nodeExists($name)) {
			$root->get($name)->delete();
		}
	}

	/**
	 * Check if a given node is a folder
	 *
	 * @param Node $node The node to check
	 * @return boolean true, if the node is representing a folder
	 */
	public function isFolder(Node $node): bool {
		return $node->getType() === FileInfo::TYPE_FOLDER;
	}

	protected function checkPermissions(Node $folder, int $mask): bool {
		$permissions = $folder->getPermissions();
		return ($permissions & $mask) === $mask;
	}

	/**
	 * Check if the user has permission to read a node on the filesystem
	 *
	 * @param Node $node The node to check
	 * @return boolean true if the user is allowed to read the node
	 */
	public function nodeHasReadPermissions(Node $node): bool {
		return $this->checkPermissions($node, self::MASK_READ);
	}

	/**
	 * Check if the user has the permission to alter the contents of
	 * a folder.
	 *
	 * @param Folder $folder The folder to check the permissions for.
	 * @return boolean true if the user is allowed to create and remove files/folders within
	 * @todo The exact rights in the mask must be checked!
	 */
	public function folderHasWritePermissions(Folder $folder): bool {
		return $this->checkPermissions($folder, self::MASK_WRITE);
	}

	/**
	 * Check if the user has the permission to alter the contents of
	 * a folder and to read it.
	 *
	 * @param Folder $folder The folder to check the permissions for.
	 * @return boolean true if the user has full access to the files within
	 * @todo The exact rights in the mask must be checked!
	 */
	public function folderHasFullPermissions(Folder $folder): bool {
		return $this->checkPermissions($folder, self::MASK_ALL);
	}
}
