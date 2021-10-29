<?php

namespace OCA\Cookbook\Service;

use OCP\Image;

class ThumbnailService {
	protected function getNewImage(): Image {
		return new Image();
	}

	public function getThumbnailMainSize(string $data): string {
		return $this->createThumbnail($data, 256);
	}

	public function getThumbnailMiniSize(string $data): string {
		return $this->createThumbnail($data, 16);
	}

	protected function createThumbnail(string $data, int $size): string {
		$img = $this->getNewImage();

		// Store to temp location
		$tmpFile = tmpfile();
		fwrite($tmpFile, $data);
		fflush($tmpFile);

		// Get the file name
		$filename = stream_get_meta_data($tmpFile)['uri'];

		$img->loadFromFile($filename);

		$img->fixOrientation();
		$img->resize($size);
		$img->centerCrop();

		return $img->data();
	}
}
