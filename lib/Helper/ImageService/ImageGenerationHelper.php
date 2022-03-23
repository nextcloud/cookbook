<?php

namespace OCA\Cookbook\Helper\ImageService;

use OCP\Files\File;
use OCP\Files\Folder;
use OCA\Cookbook\Service\ThumbnailService;

/**
 * This class provides heler function to generate appropriate thumbnails according to the needs.
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
	 * @param int $type The requested size of the thunbmail
	 * @param File $dstFile The name of the file to store the thumbnail to
	 * @return void
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
	 * This method checks if a certain thumbail size is present in the recipe folder and removes the file accordingly.
	 * Note: If the thumbnail is not present, this method does nothing.
	 *
	 * The main image will not be dropped.
	 *
	 * @param Folder $recipeFolder The folder containing the recipe
	 * @param integer $type The type of the thumbnail to remove
	 * @return void
	 */
	public function drop(Folder $recipeFolder, int $type): void {
		if ($type === ImageSize::PRIMARY_IMAGE) {
			return;
		}
		
		$filename = ImageSize::NAME_MAP[$type];

		if ($recipeFolder->nodeExists($filename)) {
			$recipeFolder->get($filename)->delete();
		}
	}
}
