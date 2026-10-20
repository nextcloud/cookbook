<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Helper\ImageService;

use OCA\Cookbook\Helper\ImageService\ImageConversionHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers OCA\Cookbook\Helper\ImageService\ImageConversionHelper
 */
class ImageConversionHelperTest extends TestCase {
	private const AVIF_FIXTURE = __DIR__ . '/res/red.avif';

	/**
	 * @var LoggerInterface&MockObject
	 */
	private $logger;

	/**
	 * @var ImageConversionHelper
	 */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$this->logger = $this->createMock(LoggerInterface::class);
		$this->dut = new ImageConversionHelper($this->logger);
	}

	private function createHelperWithUnreadableImage(): ImageConversionHelper&MockObject {
		$helper = $this->getMockBuilder(ImageConversionHelper::class)
			->onlyMethods(['isReadable', 'convertToJpeg'])
			->setConstructorArgs([$this->logger])
			->getMock();
		$helper->method('isReadable')->willReturn(false);
		return $helper;
	}

	public function testReadableImagePassesThrough(): void {
		// Given a valid PNG
		$img = imagecreatetruecolor(4, 4);
		ob_start();
		imagepng($img);
		$png = ob_get_clean();

		// When we process it
		$result = $this->dut->getReadableImageData($png);

		// Then the original data is returned unchanged
		$this->assertSame($png, $result);
	}

	public function testNonImageNonHeifReturnsNull(): void {
		// Given data that is neither a valid image nor a HEIF container
		$garbage = 'not an image';

		// Then a warning is logged
		$this->logger->expects($this->once())->method('warning');

		// When we process it — then null is returned
		$this->assertNull($this->dut->getReadableImageData($garbage));
	}

	public function testHeifIsConvertedToJpeg(): void {
		// Given an AVIF image that GD cannot read, and conversion succeeds
		$fakeJpeg = "\xFF\xD8\xFF\xE0jpeg-data";
		$helper = $this->createHelperWithUnreadableImage();
		$helper->expects($this->once())->method('convertToJpeg')->willReturn($fakeJpeg);

		$avif = file_get_contents(self::AVIF_FIXTURE);

		// When we process it
		$result = $helper->getReadableImageData($avif);

		// Then the converted JPEG is returned
		$this->assertSame($fakeJpeg, $result);
	}

	public function testFailedConversionReturnsNull(): void {
		// Given an AVIF image whose conversion fails
		$helper = $this->createHelperWithUnreadableImage();
		$helper->method('convertToJpeg')->willReturn(null);

		$avif = file_get_contents(self::AVIF_FIXTURE);

		// When we process it — then null is returned
		$this->assertNull($helper->getReadableImageData($avif));
	}
}
