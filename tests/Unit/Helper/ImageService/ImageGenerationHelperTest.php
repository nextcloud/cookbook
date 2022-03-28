<?php

namespace OCA\Cookbook\tests\Unit\Helper\ImageService;

use OCA\Cookbook\Helper\ImageService\ImageGenerationHelper;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Helper\ImageService\ImageSize;
use OCA\Cookbook\Service\ThumbnailService;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * @covers OCA\Cookbook\Helper\ImageService\ImageGenerationHelper
 */
class ImageGenerationHelperTest extends TestCase {

	/**
	 * @var ThumbnailService|MockObject
	 */
	private $thumbnailService;

	/**
	 * @var ImageGenerationHelper
	 */
	private $dut;

	protected function setUp(): void {
		$this->thumbnailService = $this->createMock(ThumbnailService::class);

		$this->dut = new ImageGenerationHelper($this->thumbnailService);
	}

	public function dpThumbnails() {
		yield [ImageSize::THUMBNAIL];
		yield [ImageSize::MINI_THUMBNAIL];
	}

	/**
	 * @dataProvider dpThumbnails
	 */
	public function testThumbnailGeneration($type) {
		/**
		 * @var Stub|File $fullImage
		 */
		$fullImage = $this->createStub(File::class);
		/**
		 * @var MockObject|File
		 */
		$dstFile = $this->createMock(File::class);

		$fullContent = 'The content of the full image';
		$thumbContent = 'The content of the thumb file';

		$fullImage->method('getContent')->willReturn($fullContent);

		$fileWasSaved = true;

		$dstFile->expects($this->once())->method('putContent')->with($thumbContent)->will(
			$this->returnCallback(function ($content) use (&$fileWasSaved) {
				$fileWasSaved = false;
			})
		);
		$dstFile->expects($this->once())->method('touch')->will($this->returnCallback(
			function () use (&$fileWasSaved) {
				$fileWasSaved = true;
			}
		));

		$this->thumbnailService->method('getThumbnail')->with($fullContent, $type)
			->willReturn($thumbContent);

		$this->dut->generateThumbnail($fullImage, $type, $dstFile);

		$this->assertTrue($fileWasSaved, 'File was not touched after modifications.');
	}

	public function testFullSizeImage() {
		$fullImage = $this->createStub(File::class);
		/**
		 * @var MockObject|File $dstFile
		 */
		$dstFile = $this->createMock(File::class);

		$dstFile->expects($this->never())->method('putContent');

		$this->dut->generateThumbnail($fullImage, ImageSize::PRIMARY_IMAGE, $dstFile);
	}

	public function dpDropExisting() {
		yield [ImageSize::THUMBNAIL, 'thumb.jpg'];
		yield [ImageSize::MINI_THUMBNAIL, 'thumb16.jpg'];
	}

	/**
	 * @dataProvider dpDropExisting
	 */
	public function testDropThumbnailExisting($type, $filename) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);
		/**
		 * @var MockObject|File $thumbnail
		 */
		$thumbnail = $this->createMock(File::class);

		$folder->method('get')->with($filename)->willReturn($thumbnail);
		$folder->method('nodeExists')->willReturn(true);

		$thumbnail->expects($this->once())->method('delete');

		$this->dut->drop($folder, $type);
	}

	/**
	 * @dataProvider dpDropExisting
	 */
	public function testDropThumbnailNonExisting($type, $filename) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);

		$folder->method('get')->with($filename)->willThrowException(new NotFoundException());
		$folder->method('nodeExists')->willReturn(false);

		$this->dut->drop($folder, $type);
		$this->assertTrue(true, 'No Exception was thrown');
	}

	public function testDropThumbnailMainImage() {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);
		/**
		 * @var MockObject|Folder $image
		 */
		$image = $this->createMock(File::class);

		$folder->method('get')->with('full.jpg')->willReturn($image);
		$folder->method('nodeExists')->willReturn(true);

		$image->expects($this->never())->method('delete');

		$this->dut->drop($folder, ImageSize::PRIMARY_IMAGE);
		$this->assertTrue(true, 'No Exception was thrown');
	}
}
