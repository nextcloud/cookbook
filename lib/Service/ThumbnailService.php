<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Exception\InvalidThumbnailTypeException;
use OCA\Cookbook\Helper\ImageService\ImageSize;
use OCP\IL10N;
use OCP\ILogger;
use OCP\Image;

/**
 * This class carries out the creation of the thumbnail images for the recipes.
 *
 * It uses only the in-memory representations to avoid file IO if not needed.
 * You need to store the images if they should be preserved.
 */
class ThumbnailService {
	/**
	 * @var IL10N
	 */
	private $l;

	/** @var ILogger */
	private $logger;

	public function __construct(IL10N $l, ILogger $logger) {
		$this->l = $l;
		$this->logger = $logger;
	}

	/**
	 * Generate a new image from the NC core.
	 *
	 * This is needed to tap into the class during testing.
	 *
	 * @return Image An empty image as generated by `new Image()`
	 */
	protected function getNewImage(): Image {
		return new Image();
	}

	/**
	 * Create a thumbnail for a requested thumbnail size
	 *
	 * @param string $data The image data to be rescaled
	 * @param int $type The requested type, see the ImageSize class
	 * @return string The image data of the generated thumbnail
	 * @throws InvalidThumbnailTypeException if the requested type is either unknown or useless.
	 */
	public function getThumbnail(string $data, int $type): string {
		$this->logger->debug("Create thumbnail of type $type");
		switch ($type) {
			case ImageSize::THUMBNAIL:
				return $this->createThumbnail($data, 256);
			case ImageSize::MINI_THUMBNAIL:
				return $this->createThumbnail($data, 16);
			case ImageSize::PRIMARY_IMAGE:
				throw new InvalidThumbnailTypeException($this->l->t('The full-sized image is not a thumbnail.'));
			default:
				throw new InvalidThumbnailTypeException($this->l->t('The thumbnail type %d is not known.', [$type]));
		}
	}

	/**
	 * Create a new thumbnail of a given size
	 *
	 * @param string $data The image data
	 * @param int $size The maximal width or height of the destination image
	 * @return string The resized and minimized image
	 */
	protected function createThumbnail(string $data, int $size): string {
		$img = $this->getNewImage();

		// Store to temp location
		$tmpFile = tmpfile();
		fwrite($tmpFile, $data);
		fflush($tmpFile);

		// Get the file name
		$filename = stream_get_meta_data($tmpFile)['uri'];
		$this->logger->debug("File name of temporary file is $filename.");
		$this->logger->debug("File stats:\n" . print_r(fstat($tmpFile), true));

		$img->loadFromFile($filename);

		$this->logger->debug("Failed loading local file: " . ($img === false));

		$img->fixOrientation();
		$img->resize($size);
		$img->centerCrop();

		$this->logger->debug("Image size: ". strlen($img->data()));
		
		return $img->data();
	}
}
