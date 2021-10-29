<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Exception\InvalidThumbnailTypeException;
use OCA\Cookbook\Exception\RecipeImageExistsException;
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

	/**
	 * @var ThumbnailService
	 */
	private $thumbnailService;

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(ThumbnailService $thumbnailService, IL10N $l) {
		$this->thumbnailService = $thumbnailService;
		$this->l = $l;
	}

	/**
	 * Get the main image from a recipe
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @return File The image file
	 * @throws NotFoundException if no image has been found
	 */
	public function getImage(Folder $recipeFolder): File {
		return $recipeFolder->get('full.jpg');
	}

	/**
	 * Get a thumnail of a recipe
	 *
	 * The type of the thumbnail can be THUMB_MAIN and THUMB_16.
	 * Any other value will trigger a InvalidThumbnailTypeException.
	 *
	 * If the requested thumbnail is not present, it will be generated.
	 *
	 * @param Folder $recipeFolder The folder of the recipe
	 * @param integer $type The type of the requested thumbnail
	 * @return File The thumbnail file
	 * @throws InvalidThumbnailTypeException if the requested thumbaail type is not known.
	 * @throws NotFoundException if there is no image associated with the recipe.
	 */
	public function getThumbnail(Folder $recipeFolder, int $type): File {
		$fileName = $this->getFileName($type);

		try {
			return $recipeFolder->get($fileName);
		} catch (NotFoundException $ex) {
			return $this->generateThumb($recipeFolder, $type, $fileName);
		}
	}

	/**
	 * Create an image for a recipe
	 *
	 * @param Folder $recipeFolder The folder containing the recipe files.
	 * @return File The image object for the recipe
	 * @throws RecipeImageExistsException if the folder has already a image file present.
	 */
	public function createImage(Folder $recipeFolder): File {
		if ($recipeFolder->nodeExists('full.jpg')) {
			throw new RecipeImageExistsException(
				$this->l->t('The recipe has already an image file. Cannot create a new one.')
			);
		}

		return $recipeFolder->get('full.jpg');
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
		$fullImg = $this->getImage($recipeFolder);
		$fullContent = $fullImg->getContent();

		$thumbContent = $this->thumbnailService->getThumbnailMainSize($fullContent);
		$miniContent = $this->thumbnailService->getThumbnailMiniSize($thumbContent);

		if ($recipeFolder->nodeExists('thumb.jpg')) {
			/**
			 * @var File $file
			 */
			$file = $recipeFolder->get('thumb.jpg');
			$file->putContent($thumbContent);
		} else {
			$recipeFolder->newFile('thumb.jpg')->putContent($thumbContent);
		}
		
		if ($recipeFolder->nodeExists('thumb16.jpg')) {
			/**
			 * @var File $file
			 */
			$file = $recipeFolder->get('thumb16.jpg');
			$file->putContent($miniContent);
		} else {
			$recipeFolder->newFile('thumb16.jpg')->putContent($miniContent);
		}
	}

	private function getFileName(int $type): string {
		switch ($type) {
			case self::THUMB_MAIN:
				return 'thumb.jpg';
			case self::THUMB_16:
				return 'thumb16.jpg';
			default:
				throw new InvalidThumbnailTypeException(
					$this->l->t('Unknown type %d found.', [$type])
				);
		}
	}

	private function generateThumb(Folder $recipeFolder, int $type, string $fileName): File {
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
		
		$thumb = $recipeFolder->newFile($fileName);
		$thumb->putContent($thumbContent);
		
		return $thumb;
	}
}
