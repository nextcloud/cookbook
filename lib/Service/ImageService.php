<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Helper\ImageService\ImageFileHelper;
use OCA\Cookbook\Helper\ImageService\ThumbnailFileHelper;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;

/**
 * This class provides an interface to access the images of the recipes.
 * This simplifies/abstracts the access of the images to avoid low-level file system access.
 *
 * @todo Use Abstract Filesystem
 */
class ImageService {

	/**
	 * @var ThumbnailFileHelper
	 */
	private $thumbnailHelper;

	/**
	 * @var ImageFileHelper
	 */
	private $fileHelper;

	public function __construct(
		ImageFileHelper $fileHelper,
		ThumbnailFileHelper $thumbnailHelper
		) {
		$this->fileHelper = $fileHelper;
		$this->thumbnailHelper = $thumbnailHelper;
	}

	/**
	 * Get the main image from a recipe
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @return string The image file data
	 * @throws NotFoundException if no image has been found
	 */
	public function getImage(Folder $recipeFolder): string {
		return $this->getImageAsFile($recipeFolder)->getContent();
	}

	/**
	 * Get the main image of a recipe as a file for further processing
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @return File The file containing the main image of the recipe
	 * @throws NotFoundException if no image has been found
	 */
	public function getImageAsFile(Folder $recipeFolder): File {
		return $this->fileHelper->getImage($recipeFolder);
	}

	/**
	 * Check if a recipe has a folder attached
	 *
	 * @param Folder $recipeFolder The folder of the recipe to check
	 * @return boolean true, if there is an image present
	 */
	public function hasImage(Folder $recipeFolder): bool {
		return $this->fileHelper->hasImage($recipeFolder);
	}

	/**
	 * Remove the image from a recipe.
	 *
	 * This will delete the primary image and all thumbnails
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @return void
	 */
	public function dropImage(Folder $recipeFolder): void {
		$this->fileHelper->dropImage($recipeFolder);
		$this->thumbnailHelper->dropThumbnails($recipeFolder);
	}

	/**
	 * Obtain a thumbnail from a recipe
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @param integer $type The type of the thumbnail to obtain
	 * @see OCA\Cookbook\Helper\ImageService\ImageSize for a list of possible image sizes
	 * @return string The image data
	 */
	public function getThumbnail(Folder $recipeFolder, int $type): string {
		return $this->getThumbnailAsFile($recipeFolder, $type)->getContent();
	}

	/**
	 * Obtain a thumbnail from a recipe as a file for further processing
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @param integer $type The type of the thumbnail to obtain
	 * @see OCA\Cookbook\Helper\ImageService\ImageSize for a list of possible image sizes
	 * @return File The file of the thumbnail
	 */
	public function getThumbnailAsFile(Folder $recipeFolder, int $type): File {
		return $this->thumbnailHelper->ensureThumbnailExists($recipeFolder, $type);
	}

	/**
	 * Store a new image in the recipe folder.
	 *
	 * This will store the datan and recreate the thumbnails to keep them up to date.
	 *
	 * @param Folder $recipeFolder The recipe folder to store the image to
	 * @param string $data The image data
	 * @return void
	 */
	public function setImageData(Folder $recipeFolder, string $data): void {
		if ($this->fileHelper->hasImage($recipeFolder)) {
			$this->fileHelper->getImage($recipeFolder)->putContent($data);
		} else {
			$this->fileHelper->createImage($recipeFolder)->putContent($data);
		}
		$this->thumbnailHelper->recreateThumbnails($recipeFolder);
	}
}
