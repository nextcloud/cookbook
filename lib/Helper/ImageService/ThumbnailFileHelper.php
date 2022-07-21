<?php

namespace OCA\Cookbook\Helper\ImageService;

use OCP\IL10N;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use OCA\Cookbook\Exception\NoRecipeImageFoundException;
use OCP\Files\NotPermittedException;

/**
 * This class allows to handle the files of the thumbnails
 */
class ThumbnailFileHelper {
	/**
	 * @var ImageGenerationHelper
	 */
	private $generationHelper;

	/**
	 * @var ImageFileHelper
	 */
	private $fileHelper;

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(
		ImageGenerationHelper $generationHelper,
		ImageFileHelper $fileHelper,
		IL10N $l
	) {
		$this->generationHelper = $generationHelper;
		$this->fileHelper = $fileHelper;
		$this->l = $l;
	}

	/**
	 * Ensure that a thumbnail for a certain size exists and returns it
	 *
	 * @param Folder $recipeFolder The folder of the recipe to check for
	 * @param int $type The type of the thumbnail to generate
	 * @return File The thumbnail file
	 * @throws NoRecipeImageFoundException if the recipe has no primary image to create a thumbnail from
	 * @throws NotPermittedException if the thumbnail generation could not write the thumbnail to the correct location
	 */
	public function getThumbnail(Folder $recipeFolder, int $type): File {
		$filename = ImageSize::NAME_MAP[$type];

		if ($recipeFolder->nodeExists($filename)) {
			return $recipeFolder->get($filename);
		} else {
			if ($this->fileHelper->hasImage($recipeFolder)) {
				$full = $this->fileHelper->getImage($recipeFolder);
				$file = $recipeFolder->newFile($filename);

				$this->generationHelper->generateThumbnail($full, $type, $file);
				return $file;
			} else {
				throw new NoRecipeImageFoundException(
					$this->l->t('There is no primary image for the recipe present.')
				);
			}
		}
	}

	/**
	 * Recreate a thumbnail file.
	 * Check if a thumbnail file exists and reuse it if possible.
	 * Otherwise a new file is generated.
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @param int $type The thumbnail type to generate
	 * @throws NoRecipeImageFoundException if the recipe has no primary image to create the thumbnails from
	 * @throws NotFoundException If no full-scale image was found.
	 * @throws NotPermittedException if the IO to read or write the image file was not allowed
	 * @throws LockedException if the image file was locked and thus could not be read or written
	 * @throws GenericFileException if the writing fails for some reason
	 * @throws InvalidPathException
	 * @throws InvalidThumbnailTypeException if the requested thumbnail type is not known or is useless
	 */
	private function recreateSingleThumbnail(Folder $recipeFolder, int $type): void {
		$filename = ImageSize::NAME_MAP[$type];

		if ($this->fileHelper->hasImage($recipeFolder)) {
			$full = $this->fileHelper->getImage($recipeFolder);
			if ($recipeFolder->nodeExists($filename)) {
				$file = $recipeFolder->get($filename);
			} else {
				$file = $recipeFolder->newFile($filename);
			}

			$this->generationHelper->generateThumbnail($full, $type, $file);
		} else {
			$this->getThumbnail($recipeFolder, $type);
		}
	}

	/**
	 * Recreate all thumbnails in the recipe.
	 *
	 * This will remove them and create new ones.
	 *
	 * @param Folder $recipeFolder The folder containing the files of a recipe.
	 * @throws NoRecipeImageFoundException if the recipe has no primary image to create the thumbnails from
	 * @throws NotFoundException If no full-scale image was found.
	 * @throws NotPermittedException if the IO to read or write the image file was not allowed
	 * @throws LockedException if the image file was locked and thus could not be read or written
	 * @throws GenericFileException if the writing fails for some reason
	 * @throws InvalidPathException
	 * @throws InvalidThumbnailTypeException if the requested thumbnail type is not known or is useless
	 */
	public function recreateThumbnails(Folder $recipeFolder): void {
		$this->recreateSingleThumbnail($recipeFolder, ImageSize::THUMBNAIL);
		$this->recreateSingleThumbnail($recipeFolder, ImageSize::MINI_THUMBNAIL);
	}

	/**
	 * Drop a thumbnail in a recipe
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @param int $type The thumbnail type to remove
	 */
	private function dropSingleThumbnail(Folder $recipeFolder, int $type): void {
		$filename = ImageSize::NAME_MAP[$type];
		if ($recipeFolder->nodeExists($filename)) {
			$recipeFolder->get($filename)->delete();
		}
	}

	/**
	 * Drop all thumbnails from a recipe folder
	 *
	 * @param Folder $recipeFolder The folder to drop the thumbnails from
	 */
	public function dropThumbnails(Folder $recipeFolder): void {
		$this->dropSingleThumbnail($recipeFolder, ImageSize::THUMBNAIL);
		$this->dropSingleThumbnail($recipeFolder, ImageSize::MINI_THUMBNAIL);
	}
}
