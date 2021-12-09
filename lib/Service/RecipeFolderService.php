<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Entity\Recipe;
use OCA\Cookbook\Helper\FilesystemHelper;
use OCA\Cookbook\Helper\RecipeFolderHelper;
use OCP\Files\NotFoundException;

/**
 * This class should allow to access the files of a single recipe in an abstract way.
 */
class RecipeFolderService {
	private const RECIPE_FILE_NAME = 'recipe.json';
	private const IMAGE_FILE_NAME = 'full.jpg';
	private const THUMBNAIL_FILE_NAME = 'thumb.jpg';
	private const THUMBNAIL16_FILE_NAME = 'thumb16.jpg';

	/** @var RecipeFolderHelper */
	private $recipeFolderHelper;

	/** @var FilesystemHelper */
	private $filesystemHelper;
	
	public function __construct(RecipeFolderHelper $rfHelper, FilesystemHelper $fsHelper) {
		$this->recipeFolderHelper = $rfHelper;
		$this->filesystemHelper = $fsHelper;
	}
	
	/**
	 * Get the original recipe content as a string.
	 *
	 * This method does no parsing or input validation.
	 *
	 * @param Recipe $recipe The recipe to read
	 * @return string The content of the main recipe JSON file
	 */
	public function getRecipe(Recipe $recipe): string {
		return $this->recipeFolderHelper->getContent($recipe->getFolder(), self::RECIPE_FILE_NAME);
	}

	/**
	 * Obtain the image for a recipe.
	 *
	 * @param Recipe $recipe The recipe to obtain the image for
	 * @return string The content of the image file
	 * @throws NotFoundException If there was no recipe image found.
	 */
	public function getImage(Recipe $recipe): string {
		return $this->recipeFolderHelper->getContent($recipe->getFolder(), self::IMAGE_FILE_NAME);
	}
	
	/**
	 * Obtain the thumbnail for a recipe.
	 *
	 * @param Recipe $recipe The recipe to obtain the image for
	 * @return string The content of the image file
	 * @throws NotFoundException If there was no recipe image found.
	 */
	public function getThumbnail(Recipe $recipe): string {
		return $this->recipeFolderHelper->getContent($recipe->getFolder(), self::THUMBNAIL_FILE_NAME);
	}
	
	/**
	 * Obtain the small thumbnail for a recipe.
	 *
	 * @param Recipe $recipe The recipe to obtain the image for
	 * @return string The content of the image file
	 * @throws NotFoundException If there was no recipe image found.
	 */
	public function getSmallThumbnail(Recipe $recipe): string {
		return $this->recipeFolderHelper->getContent($recipe->getFolder(), self::THUMBNAIL16_FILE_NAME);
	}

	/**
	 * Ensure that the recipe file is existing and put the content there.
	 *
	 * @param Recipe $recipe The recipe to put the content to
	 * @param string $data The string representation of the recipe data
	 * @return void
	 */
	public function putRecipe(Recipe $recipe, string $data): void {
		$this->createFolder($recipe);
		$this->recipeFolderHelper->putContent($recipe->getFolder(), self::RECIPE_FILE_NAME, $data);
	}
	
	/**
	 * Ensure that the recipe image file is existing and put the content there.
	 *
	 * @param Recipe $recipe The recipe to put the content to
	 * @param string $data The binary data of the iamge
	 * @return void
	 */
	public function putImage(Recipe $recipe, string $imgData): void {
		$this->createFolder($recipe);
		$this->recipeFolderHelper->putContent($recipe->getFolder(), self::IMAGE_FILE_NAME, $imgData);
	}
	
	/**
	 * Ensure that the recipe thumbnail is existing and put the content there.
	 *
	 * @param Recipe $recipe The recipe to put the content to
	 * @param string $data The binary data of the iamge
	 * @return void
	 */
	public function putThumbnail(Recipe $recipe, string $imgData): void {
		$this->createFolder($recipe);
		$this->recipeFolderHelper->putContent($recipe->getFolder(), self::THUMBNAIL_FILE_NAME, $imgData);
	}
	
	/**
	 * Ensure that the recipe small thumbnail is existing and put the content there.
	 *
	 * @param Recipe $recipe The recipe to put the content to
	 * @param string $data The binary data of the iamge
	 * @return void
	 */
	public function putSmallThumbnail(Recipe $recipe, string $imgData): void {
		$this->createFolder($recipe);
		$this->recipeFolderHelper->putContent($recipe->getFolder(), self::THUMBNAIL16_FILE_NAME, $imgData);
	}

	/**
	 * Make the recipe folder and ensure it is existing
	 *
	 * @param Recipe $recipe The recipe to create if needed
	 * @return void
	 */
	public function make(Recipe $recipe): void {
		$this->filesystemHelper->ensureFolderExists(
			$recipe->getFolderName(), $recipe->getCookbook()->getFolder());
	}

	/**
	 * Remove all images from the recipe folder
	 *
	 * @param Recipe $recipe The recipe to alter
	 * @return void
	 */
	public function removeImages(Recipe $recipe): void {
		$this->filesystemHelper->ensureNodeDeleted(self::IMAGE_FILE_NAME, $recipe->getFolder());
		$this->filesystemHelper->ensureNodeDeleted(self::THUMBNAIL_FILE_NAME, $recipe->getFolder());
		$this->filesystemHelper->ensureNodeDeleted(self::THUMBNAIL16_FILE_NAME, $recipe->getFolder());
	}

	/**
	 * Remove the complete recipe folder from the data storage
	 *
	 * @param Recipe $recipe The recipe to remove
	 * @return void
	 */
	public function remove(Recipe $recipe): void {
		$recipe->getFolder()->delete();
	}

	/**
	 * Ensure the recipe folder exists
	 *
	 * @param Recipe $recipe The recipe to ensure existing
	 * @return void
	 * @todo This might be better off in a CookookFolderService class
	 */
	public function createFolder(Recipe $recipe): void {
		$cookbook = $recipe->getCookbook();

		$folder = $cookbook->getRecipeFolder($recipe->getFolderName());
		if ($folder === null) {
			$folder = $this->filesystemHelper->ensureFolderExists(
				$recipe->getFolderName(), $cookbook->getFolder());
			$cookbook->cacheRecipeFolder($recipe->getFolderName(), $folder);
		}
	}
}
