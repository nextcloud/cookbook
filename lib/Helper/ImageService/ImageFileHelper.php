<?php

namespace OCA\Cookbook\Helper\ImageService;

use OCP\IL10N;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use OCA\Cookbook\Exception\RecipeImageExistsException;
use OCP\Files\NotPermittedException;

class ImageFileHelper {
	private const NAME_MAIN = ImageSize::NAME_MAP[ImageSize::PRIMARY_IMAGE];

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(
		IL10N $l
		) {
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
	 * Check if a recipe folder contains an image
	 *
	 * @param Folder $recipeFolder The folder of the recipe to check
	 * @return bool true, if there is an image present
	 */
	public function hasImage(Folder $recipeFolder): bool {
		return $recipeFolder->nodeExists(self::NAME_MAIN);
	}

	/**
	 * Drop the image of a recipe
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 */
	public function dropImage(Folder $recipeFolder): void {
		if ($recipeFolder->nodeExists(self::NAME_MAIN)) {
			$recipeFolder->get(self::NAME_MAIN)->delete();
		}
	}

	/**
	 * Create an image for a recipe
	 *
	 * @param Folder $recipeFolder The folder containing the recipe files.
	 * @return File The image object for the recipe
	 * @throws RecipeImageExistsException if the folder has already a image file present.
	 * @throws NotPermittedException If the image file could not be generated.
	 */
	public function createImage(Folder $recipeFolder): File {
		if ($this->hasImage($recipeFolder)) {
			throw new RecipeImageExistsException(
				$this->l->t('The recipe has already an image file. Cannot create a new one.')
			);
		} else {
			return $recipeFolder->newFile(self::NAME_MAIN);
		}
	}
}
