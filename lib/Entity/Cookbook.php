<?php

namespace OCA\Cookbook\Entity;

use OCA\Cookbook\Helper\UserFolderHelper;
use OCP\Files\Folder;

class Cookbook {

	/**
	 * @var UserFolderHelper
	 */
	private $userFolderHelper;

	/** @var array */
	private $recipeFolderCache;

	public function __construct(UserFolderHelper $userFolderHelper) {
		$this->userFolderHelper = $userFolderHelper;
	}

	public function getFolder(): Folder {
		return $this->userFolderHelper->getFolder();
	}

	public function getRecipeFolder(string $folderName): ?Folder {
		if (array_key_exists($folderName, $this->recipeFolderCache)) {
			return $this->recipeFolderCache[$folderName];
		}
		return null;
	}

	public function cacheRecipeFolder(string $folderName, Folder $folder): void {
		$this->recipeFolderCache[$folderName] = $folder;
	}
}
