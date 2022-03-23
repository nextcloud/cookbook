<?php

namespace OCA\Cookbook\Helper\ImageService;

class ImageSize {
	public const PRIMARY_IMAGE = 1;
	public const THUMBNAIL = 2;
	public const MINI_THUMBNAIL = 3;

	public const NAME_MAP = [
		self::PRIMARY_IMAGE => 'full.jpg',
		self::THUMBNAIL => 'thumb.jpg',
		self::MINI_THUMBNAIL => 'thumb16.jpg',
	];
}
