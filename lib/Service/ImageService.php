<?php

namespace OCA\Cookbook\Service;

use Icewind\SMB\Exception\InvalidTypeException;
use OCA\Cookbook\Exception\InvalidThumbnailTypeException;
use OCA\Cookbook\Exception\RecipeImageExistsException;
use OCA\Cookbook\Helper\FilesystemHelper;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use OCP\IL10N;

/**
 * This class provides an interface to access the images of the recipes.
 * This simplifies/abstracts the access of the images to avoid low-level file system access.
 *
 * @todo Use Abstract Filesystem
 */
class ImageService {
	public const THUMB_MAIN = 1;
	public const THUMB_16 = 2;

	private const NAME_MAIN = 'full.jpg';
	private const NAME_THUMB = 'thumb.jpg';
	private const NAME_MINI = 'thumb16.jpg';

	/**
	 * @var ThumbnailService
	 */
	private $thumbnailService;

	/**
	 * @var FilesystemHelper
	 */
	private $fs;

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(ThumbnailService $thumbnailService, FilesystemHelper $fsHelper, IL10N $l) {
		$this->thumbnailService = $thumbnailService;
		$this->fs = $fsHelper;
		$this->l = $l;
	}

	/**
	 * Get the main image from a recipe
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @return File The image file
	 * @throws NotFoundException if no image has been found
	 * @todo Do not use low-level functions of NC core here.
	 */
	public function getImage(Folder $recipeFolder): File {
		return $recipeFolder->get(self::NAME_MAIN);
	}

	/**
	 * Check if a recipe has a folder attached
	 *
	 * @param Folder $recipeFolder The folder of the recipe to check
	 * @return boolean true, if there is an image present
	 */
	public function hasImage(Folder $recipeFolder): bool {
		return $this->fs->nodeExists(self::NAME_MAIN, $recipeFolder);
	}

	/**
	 * Ensure that a thumbnail for a certain size exists
	 *
	 * @param Folder $recipeFolder The folder of the recipe to check for
	 * @param integer $type The type of the thumbnail to generate
	 * @return File The thumbnail file
	 */
	public function ensureThumbnailExists(Folder $recipeFolder, int $type): File {
		$filename = $this->getFileName($type);
		$file = $this->fs->ensureFileExists($filename, $recipeFolder);
		if ($file->getSize() === 0) {
			// Build thumbnail
			$this->generateThumb($recipeFolder, $type, $file);
		}
		return $file;
	}

	/**
	 * Create an image for a recipe
	 *
	 * @param Folder $recipeFolder The folder containing the recipe files.
	 * @return File The image object for the recipe
	 * @throws RecipeImageExistsException if the folder has already a image file present.
	 */
	public function createImage(Folder $recipeFolder): File {
		$file = $this->fs->ensureFileExists(self::NAME_MAIN, $recipeFolder);

		if ($file->getSize() > 0) {
			throw new RecipeImageExistsException(
				$this->l->t('The recipe has already an image file. Cannot create a new one.')
			);
		}

		return $file;
	}

	/**
	 * Recreate all thumbnails in the recipe.
	 *
	 * This will remove them and create new ones.
	 *
	 * @param Folder $recipeFolder The folder containing the files of a recipe.
	 * @return void
	 * @throws NotFoundException If no full-scale image was found.
	 */
	public function recreateThumbnails(Folder $recipeFolder): void {
		$this->fs->ensureNodeDeleted(self::NAME_THUMB, $recipeFolder);
		$this->fs->ensureNodeDeleted(self::NAME_MINI, $recipeFolder);

		$this->ensureThumbnailExists($recipeFolder, self::THUMB_MAIN);
		$this->ensureThumbnailExists($recipeFolder, self::THUMB_16);
	}

	/**
	 * Get the name of the image stored relative to the recipe folder for the given thumbnail size
	 *
	 * @param integer $type The thumbnail size as defined in the class' constants
	 * @return string The name of the file
	 * @throws InvalidTypeException If the named type is unknown
	 */
	private function getFileName(int $type): string {
		switch ($type) {
			case self::THUMB_MAIN:
				return self::NAME_THUMB;
			case self::THUMB_16:
				return self::NAME_MINI;
			default:
				throw new InvalidThumbnailTypeException(
					$this->l->t('Unknown type %d found.', [$type])
				);
		}
	}

	protected function generateThumb(Folder $recipeFolder, int $type, File $dstFile): void {
		$full = $this->getImage($recipeFolder);
		$fullContent = $full->getContent();

		switch ($type) {
			case self::THUMB_MAIN:
				$thumbContent = $this->thumbnailService->getThumbnailMainSize($fullContent);
				break;
			case self::THUMB_16:
				$thumbContent = $this->thumbnailService->getThumbnailMiniSize($fullContent);
				break;
		}
		
		$dstFile->putContent($thumbContent);
	}
}
