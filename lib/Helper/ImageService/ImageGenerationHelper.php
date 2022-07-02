<?php

namespace OCA\Cookbook\Helper\ImageService;

use OCA\Cookbook\Exception\InvalidThumbnailTypeException;
use OCP\Files\File;
use OCP\Files\Folder;
use OCA\Cookbook\Service\ThumbnailService;
use OCP\Files\GenericFileException;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Lock\LockedException;

/**
 * This class provides helper function to generate appropriate thumbnails according to the needs.
 */
class ImageGenerationHelper {
	private const MAP = [
		ImageSize::PRIMARY_IMAGE => 'full.jpg',
		ImageSize::THUMBNAIL => 'thumb.jpg',
		ImageSize::MINI_THUMBNAIL => 'thumb16.jpg',
	];

	/**
	 * @var ThumbnailService
	 */
	private $thumbnailService;

	public function __construct(
		ThumbnailService $thumbnailService
	) {
		$this->thumbnailService = $thumbnailService;
	}

	/**
	 * Calculate the image data of a thumbnail of defined size and store it in the nextcloud server storage.
	 *
	 * @param File $fullImage The full-sized image to use as a starting point
	 * @param int $type The requested size of the thumbnail
	 * @param File $dstFile The name of the file to store the thumbnail to
	 * @throws NotPermittedException if the IO to read or write the image file was not allowed
	 * @throws LockedException if the image file was locked and thus could not be read or written
	 * @throws GenericFileException if the writing fails for some reason
	 * @throws NotFoundException
	 * @throws InvalidPathException
	 * @throws InvalidThumbnailTypeException if the requested thumbnail type is not known or is useless
	 */
	public function generateThumbnail(File $fullImage, int $type, File $dstFile): void {
		if ($type === ImageSize::PRIMARY_IMAGE) {
			return;
		}

		$fullContent = $fullImage->getContent();

		$thumbContent = $this->thumbnailService->getThumbnail($fullContent, $type);

		$dstFile->putContent($thumbContent);
		$dstFile->touch();
	}

	/**
	 * Ensure that a thumbnail is not existing in the file system.
	 *
	 * This method checks if a certain thumbnail size is present in the recipe folder and removes the file accordingly.
	 * Note: If the thumbnail is not present, this method does nothing.
	 *
	 * The main image will not be dropped.
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @param int $type The type of the thumbnail to remove
	 * @throws NotPermittedException if the image could not be removed
	 * @throws InvalidPathException
	 */
	public function drop(Folder $recipeFolder, int $type): void {
		if ($type === ImageSize::PRIMARY_IMAGE) {
			return;
		}

		$filename = ImageSize::NAME_MAP[$type];

		try {
			$recipeFolder->get($filename)->delete();
		} catch (NotFoundException $ex) {
			// This is ok, the file was not found, so it is already removed
		}
	}
}
