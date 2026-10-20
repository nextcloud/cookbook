<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Helper\ImageService;

use OCA\Cookbook\Helper\ImageService\ImageConversionHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\ImageService\ImageConversionHelper
 */
class ImageConversionHelperTest extends TestCase {
	/**
	 * @var ImageConversionHelper
	 */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$this->dut = new ImageConversionHelper();
	}

	public function testReadableImageIsKept() {
		$img = imagecreatetruecolor(4, 4);
		ob_start();
		imagepng($img);
		$png = ob_get_clean();

		$this->assertSame($png, $this->dut->getReadableImageData($png));
	}

	public function testInvalidDataIsRejected() {
		$this->assertNull($this->dut->getReadableImageData('<html>Not an image</html>'));
	}

	public function testInvalidHeifDataIsRejected() {
		$this->assertNull($this->dut->getReadableImageData("\0\0\0\x1cftypavif" . str_repeat("\0", 32)));
	}

	public function testAvifIsConvertedToJpeg() {
		if (!class_exists(\Imagick::class) || \Imagick::queryFormats('AVIF') === []) {
			$this->markTestSkipped('Imagick with AVIF support is not available.');
		}
		if (imagetypes() & IMG_AVIF) {
			$this->markTestSkipped('GD can read AVIF, so no conversion is needed.');
		}

		$avif = file_get_contents(__DIR__ . '/res/red.avif');

		$result = $this->dut->getReadableImageData($avif);

		$this->assertNotNull($result);
		$this->assertSame("\xFF\xD8\xFF", substr($result, 0, 3));
	}
}
