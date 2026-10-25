<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\ImageService;

use OCP\Image;
use Psr\Log\LoggerInterface;

/**
 * This class ensures that image data can be processed by the thumbnail generation.
 */
class ImageConversionHelper {
	private const HEIF_BRANDS = ['avif', 'avis', 'heic', 'heix', 'heim', 'heis', 'hevc', 'hevm', 'mif1', 'msf1'];

	public function __construct(private LoggerInterface $logger) {
	}

	/**
	 * Get image data that the Nextcloud image library can read.
	 *
	 * AVIF and HEIC images are converted to JPEG using Imagick if the image library cannot read them.
	 *
	 * @param string $data The raw image data
	 * @return ?string The readable image data or null if the data cannot be used as an image
	 */
	public function getReadableImageData(string $data): ?string {
		if ($this->isReadable($data)) {
			return $data;
		}

		if (!$this->isHeifContainer($data)) {
			$this->logger->warning('Image is not readable and not a known HEIF container; skipping.');
			return null;
		}

		return $this->convertToJpeg($data);
	}

	protected function isReadable(string $data): bool {
		return (new Image())->loadFromData($data) !== false;
	}

	protected function convertToJpeg(string $data): ?string {
		if (!class_exists(\Imagick::class)) {
			$this->logger->warning('Image requires conversion but Imagick is not available; skipping.');
			return null;
		}

		try {
			$imagick = new \Imagick();
			$imagick->readImageBlob($data);
			$imagick->setImageFormat('jpeg');
			return $imagick->getImageBlob();
		} catch (\ImagickException $ex) {
			$this->logger->warning('Imagick failed to convert HEIF image to JPEG.', ['exception' => $ex]);
			return null;
		}
	}

	private function isHeifContainer(string $data): bool {
		return substr($data, 4, 4) === 'ftyp' && in_array(substr($data, 8, 4), self::HEIF_BRANDS, true);
	}
}
