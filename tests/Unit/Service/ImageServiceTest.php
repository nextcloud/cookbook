<?php

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Helper\ImageService\ImageFileHelper;
use OCA\Cookbook\Helper\ImageService\ImageSize;
use OCA\Cookbook\Helper\ImageService\ThumbnailFileHelper;
use OCP\Files\File;
use OCP\Files\Folder;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\ImageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * @covers \OCA\Cookbook\Service\ImageService
 */
class ImageServiceTest extends TestCase {

	/**
	 * @var MockObject|ImageFileHelper
	 */
	private $fileHelper;

	/**
	 * @var MockObject|ThumbnailFileHelper
	 */
	private $thumbnailHelper;

	/**
	 * @var ImageService|MockObject
	 */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$this->fileHelper = $this->createMock(ImageFileHelper::class);
		$this->thumbnailHelper = $this->createMock(ThumbnailFileHelper::class);

		$this->dut = new ImageService($this->fileHelper, $this->thumbnailHelper);
	}

	public function testGetImageAsFile() {
		$recipeFolder = $this->createStub(Folder::class);
		$file = $this->createStub(File::class);
		$this->fileHelper->method('getImage')->with($recipeFolder)->willReturn($file);
		$this->assertSame($file, $this->dut->getImageAsFile($recipeFolder));
	}

	public function testGetImage() {
		$recipeFolder = $this->createStub(Folder::class);
		/**
		 * @var Stub|File $file
		 */
		$file = $this->createStub(File::class);
		$content = 'This is the content of the file.';
		$file->method('getContent')->willReturn($content);
		$this->fileHelper->method('getImage')->with($recipeFolder)->willReturn($file);

		$this->assertEquals($content, $this->dut->getImage($recipeFolder));
	}

	public function dpThumbnailSizes() {
		return [
			[ ImageSize::THUMBNAIL ],
			[ ImageSize::MINI_THUMBNAIL ],
		];
	}

	/**
	 * @dataProvider dpThumbnailSizes
	 */
	public function testGetThumbnailAsFile($type) {
		$recipeFolder = $this->createStub(Folder::class);
		$file = $this->createStub(File::class);
		$this->thumbnailHelper->method('getThumbnail')->with($recipeFolder, $type)->willReturn($file);
		$this->assertSame($file, $this->dut->getThumbnailAsFile($recipeFolder, $type));
	}

	/**
	 * @dataProvider dpThumbnailSizes
	 */
	public function testGetThumbnail($type) {
		$recipeFolder = $this->createStub(Folder::class);
		/**
		 * @var Stub|File $file
		 */
		$file = $this->createStub(File::class);
		$content = 'This is the content of the file.';
		$file->method('getContent')->willReturn($content);
		$this->thumbnailHelper->method('getThumbnail')->with($recipeFolder, $type)->willReturn($file);

		$this->assertEquals($content, $this->dut->getThumbnail($recipeFolder, $type));
	}

	public function dpHasImage() {
		return [[true], [false]];
	}

	/**
	 * @dataProvider dpHasImage
	 */
	public function testHasImage($present) {
		$recipeFolder = $this->createStub(Folder::class);
		$this->fileHelper->method('hasImage')->with($recipeFolder)->willReturn($present);
		$this->assertEquals($present, $this->dut->hasImage($recipeFolder));
	}

	public function testDropAllImages() {
		$recipeFolder = $this->createStub(Folder::class);
		$this->fileHelper->expects($this->once())->method('dropImage')->with($recipeFolder);
		$this->thumbnailHelper->expects($this->once())->method('dropThumbnails')->with($recipeFolder);
		$this->dut->dropImage($recipeFolder);
	}

	public function testSetImageExisting() {
		$recipeFolder = $this->createStub(Folder::class);
		/**
		 * @var MockObject|File $image
		 */
		$image = $this->createMock(File::class);
		$data = 'The content of the image';

		$this->fileHelper->method('hasImage')->with($recipeFolder)->willReturn(true);
		$this->fileHelper->method('getImage')->with($recipeFolder)->willReturn($image);
		$image->expects($this->once())->method('putContent')->with($data);

		$this->thumbnailHelper->expects($this->once())->method('recreateThumbnails')->with($recipeFolder);

		$this->dut->setImageData($recipeFolder, $data);
	}

	public function testSetImageNonExisting() {
		$recipeFolder = $this->createStub(Folder::class);
		/**
		 * @var MockObject|File $image
		 */
		$image = $this->createMock(File::class);
		$data = 'The content of the image';

		$this->fileHelper->method('hasImage')->with($recipeFolder)->willReturn(false);
		$this->fileHelper->method('createImage')->with($recipeFolder)->willReturn($image);
		$image->expects($this->once())->method('putContent')->with($data);

		$this->thumbnailHelper->expects($this->once())->method('recreateThumbnails')->with($recipeFolder);

		$this->dut->setImageData($recipeFolder, $data);
	}
}
