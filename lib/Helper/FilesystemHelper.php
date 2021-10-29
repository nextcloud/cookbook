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

	public function __construct(IRootFolder $root) {
		$this->root = $root;
	}

	public function nodeExists(string $name, ?Folder $parent = null): bool {
		if ($parent === null) {
			$parent = $this->root;
		}
		return $parent->nodeExists($name);
	}

	public function ensureFolderExists(string $name, ?Folder $parent = null): Folder {
		$root = $parent ?? $this->root;

		try {
			$node = $root->get($name);
			if ($this->isFolder($node)) {
				return $node;
			} else {
				// TODO This shoudl be more specific
				throw new WrongFileTypeException('Path points to existing file');
			}
		} catch (NotFoundException $ex) {
			try {
				return $root->newFolder($name);
			} catch (NotPermittedException $ex2) {
				// TODO This shoudl be considered explicitly
				throw $ex2;
			}
		}
	}

	public function ensureFileExists(string $name, ?Folder $parent = null): File {
		$root = $parent ?? $this->root;

		try {
			$node = $root->get($name);
			if (! $this->isFolder($node)) {
				return $node;
			} else {
				// TODO This should be more specific
				throw new WrongFileTypeException('Path points to existing folder');
			}
		} catch (NotFoundException $ex) {
			try {
				return $root->newFile($name);
			} catch (NotPermittedException $ex2) {
				// TODO This shoudl be considered explicitly
				throw $ex2;
			}
		}
	}

	public function isFolder(Node $node): bool {
		return $node->getType() === FileInfo::TYPE_FOLDER;
	}

	protected function checkPermissions(Folder $folder, int $mask): bool {
		$permissions = $folder->getPermissions();
		return ($permissions & $mask) === $mask;
	}

	public function folderHasReadPermissions(Folder $folder): bool {
		return $this->checkPermissions($folder, self::MASK_READ);
	}

	public function folderHasWritePermissions(Folder $folder): bool {
		return $this->checkPermissions($folder, self::MASK_WRITE);
	}

	public function folderHasFullPermissions(Folder $folder): bool {
		return $this->checkPermissions($folder, self::MASK_ALL);
	}
}
