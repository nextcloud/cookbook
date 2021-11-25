<?php

namespace OCA\Cookbook\Helper;

use OCP\IL10N;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;

class RecipeFolderHelper {
	/// @var IL10N
	private $l;
	/// @var FilesystemHelper
	private $fs;

	public function __construct(IL10N $l, FilesystemHelper $fs) {
		$this->l = $l;
		$this->fs = $fs;
	}

	/**
	 * Check if a node exists in a folder
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @param string $name The name of the node to check
	 * @return boolean true, if the node with the given name exists in the recipe folder
	 */
	public function nodeExists(Folder $recipeFolder, string $name): bool {
		return $this->fs->nodeExists($name, $recipeFolder);
	}

	/**
	 * Get the content of a file within the recipe folder.
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @param string $name The name of the file to read
	 * @return string The content of the file
	 * @throws NotFoundException The named file is not found
	 */
	public function getContent(Folder $recipeFolder, string $name): string {
		/**
		 * @var File $file
		 */
		$file = $recipeFolder->get($name);
		return $file->getContent();
	}

	/**
	 * Put the content to a file in the recipe folder
	 *
	 * If the file does not yet exist it is generated.
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @param string $name The name of the file to be put
	 * @param string $content The content to put in the file
	 * @return void
	 */
	public function putContent(Folder $recipeFolder, string $name, string $content): void {
		$file = $this->fs->ensureFileExists($name, $recipeFolder);
		$file->putContent($content);
	}

	/**
	 * Remove a node from a recipe folder
	 *
	 * @param Folder $recipeFolder The recipe folder to process
	 * @param string $name The name of the file to use
	 * @return void
	 */
	public function removeFile(Folder $recipeFolder, string $name): void {
	}
}
