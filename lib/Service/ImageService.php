<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Helper\ImageService\ImageFileHelper;
use OCA\Cookbook\Helper\ImageService\ThumbnailFileHelper;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;

/**
 * This class provides an interface to access the images of the recipes.
 * This simplifies/abstracts the access of the images to avoid low-level file system access.
 *
 * @todo Use Abstract Filesystem
 * @todo Rework the exeption passing
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
	 * Get the main image of a recipe
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @return string The image file data
	 * @throws NotFoundException if no image has been found
	 * @throws LockedException if the image cannot be used currently
	 * @throws NotPermittedException if the user is not allowed to ge the file content of the image
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
	 * Check if a recipe folder contains an image
	 *
	 * @param Folder $recipeFolder The folder of the recipe to check
	 * @return bool true, if there is an image present
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
	 */
	public function dropImage(Folder $recipeFolder): void {
		$this->fileHelper->dropImage($recipeFolder);
		$this->thumbnailHelper->dropThumbnails($recipeFolder);
	}

	/**
	 * Obtain a thumbnail from a recipe
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @param int $type The type of the thumbnail to obtain
	 * @see OCA\Cookbook\Helper\ImageService\ImageSize for a list of possible image sizes
	 * @return string The image data
	 * @throws NoRecipeImageFoundException if the recipe has no primary image to create a thumbnail from
	 * @throws NotPermittedException if the thumbnail generation could not write the thumbnail to the correct location
	 * @throws LockedException if the image file is currently locked
	 * @throws NotPermittedException if the user is not permitted to read the file content of the thumbnail
	 */
	public function getThumbnail(Folder $recipeFolder, int $type): string {
		return $this->getThumbnailAsFile($recipeFolder, $type)->getContent();
	}

	/**
	 * Obtain a thumbnail of a recipe's primary image as a file for further processing
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @param int $type The type of the thumbnail to obtain
	 * @see OCA\Cookbook\Helper\ImageService\ImageSize for a list of possible image sizes
	 * @return File The file of the thumbnail
	 * @throws NoRecipeImageFoundException if the recipe has no primary image to create a thumbnail from
	 * @throws NotPermittedException if the thumbnail generation could not write the thumbnail to the correct location
	 */
	public function getThumbnailAsFile(Folder $recipeFolder, int $type): File {
		return $this->thumbnailHelper->getThumbnail($recipeFolder, $type);
	}

	/**
	 * Store a new image in the recipe folder.
	 *
	 * This will store the data and recreate the thumbnails to keep them up to date.
	 *
	 * @param Folder $recipeFolder The recipe folder to store the image to
	 * @param string $data The image data
	 * @throws NotFoundException
	 * @throws GenericFileException
	 * @throws LockedException
	 * @throws InvalidPathException
	 * @throws RecipeImageExistsException if the folder has already a image file present.
	 * @throws NotPermittedException if the image file could not be generated.
	 * @throws InvalidThumbnailTypeException if the requested thumbnail type is not known or is useless
	 * @throws NoRecipeImageFoundException if the recipe has no primary image to create the thumbnails from
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
