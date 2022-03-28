<?php

namespace OCA\Cookbook\tests\unit\Helper\ImageService;

use OCA\Cookbook\Exception\NoRecipeImageFoundException;
use OCP\IL10N;
use OCP\Files\File;
use OCP\Files\Folder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Helper\ImageService\ImageSize;
use OCA\Cookbook\Helper\ImageService\ImageFileHelper;
use OCA\Cookbook\Helper\ImageService\ThumbnailFileHelper;
use OCA\Cookbook\Helper\ImageService\ImageGenerationHelper;
use OCP\Files\NotFoundException;

/**
 * @covers OCA\Cookbook\Helper\ImageService\ThumbnailFileHelper
 * @covers OCA\Cookbook\Exception\NoRecipeImageFoundException
 */
class ThumbnailFileHelperTest extends TestCase {
	
	/**
	 * @var ThumbnailFileHelper
	 */
	private $dut;

	/**
	 * @var MockObject|ImageGenerationHelper
	 */
	private $generationHelper;

	/**
	 * @var MockObject|ImageFileHelper
	 */
	private $fileHelper;

	/**
	 * @var MockObject|Folder
	 */
	private $folder;

	protected function setUp(): void {
		/**
		 * @var Stub|IL10N $l
		 */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->generationHelper = $this->createMock(ImageGenerationHelper::class);
		$this->fileHelper = $this->createMock(ImageFileHelper::class);

		$this->dut = new ThumbnailFileHelper($this->generationHelper, $this->fileHelper, $l);
	}

	public function dpExisting() {
		yield [true];
		yield [false];
	}

	public function dpFilename() {
		yield [ImageSize::THUMBNAIL, 'thumb.jpg'];
		yield [ImageSize::MINI_THUMBNAIL, 'thumb16.jpg'];
	}

	/**
	 * @dataProvider dpFilename
	 */
	public function testGetThumbnailWithExistingThumbnail($type, $filename) {
		/**
		 * @var MockObject|Folder $f
		 */
		$f = $this->createMock(Folder::class);
		$f->method('nodeExists')->with($filename)->willReturn(true);

		$file = $this->createStub(File::class);
		$f->method('get')->with($filename)->willReturn($file);

		$this->assertSame($file, $this->dut->getThumbnail($f, $type));
	}

	/**
	 * @dataProvider dpFilename
	 */
	public function testGetThumbnailWithNonExistingThumbnail($type, $filename) {
		/**
		 * @var MockObject|Folder $f
		 */
		$f = $this->createMock(Folder::class);
		$f->method('nodeExists')->with($filename)->willReturn(false);
		$f->method('get')->with($filename)->willThrowException(new NotFoundException());

		$file = $this->createStub(File::class);
		$f->method('newFile')->with($filename)->willReturn($file);
		
		$this->fileHelper->method('hasImage')->willReturn(true);
		$full = $this->createStub(File::class);
		$this->fileHelper->method('getImage')->willReturn($full);

		$this->generationHelper->expects($this->once())->method('generateThumbnail')
			->with($full, $type, $file);

		$this->assertSame($file, $this->dut->getThumbnail($f, $type));
	}

	/**
	 * @dataProvider dpFilename
	 */
	public function testGetThumbnailWithNonExistingMainImage($type, $filename) {
		/**
		 * @var MockObject|Folder $f
		 */
		$f = $this->createMock(Folder::class);
		$f->method('nodeExists')->with($filename)->willReturn(false);
		$f->method('get')->with($filename)->willThrowException(new NotFoundException());

		$this->fileHelper->method('hasImage')->willReturn(false);
		$this->fileHelper->method('getImage')->willThrowException(new NotFoundException());

		$this->generationHelper->expects($this->never())->method('generateThumbnail');

		$this->expectException(NoRecipeImageFoundException::class);

		$this->dut->getThumbnail($f, $type);
	}

	public function dpDrop() {
		return [
			[false, false],
			[false, true],
			[true, false],
			[true, true],
		];
	}

	/**
	 * @dataProvider dpDrop
	 */
	public function testDropThumbnails($thumbExists, $miniExists) {
		/**
		 * @var MockObject|Folder $f
		 */
		$f = $this->createMock(Folder::class);
		$existMap = [
			['thumb.jpg', $thumbExists],
			['thumb16.jpg', $miniExists],
		];
		$f->method('nodeExists')->willReturnMap($existMap);

		/**
		 * @var MockObject|File $thumb
		 */
		$thumb = $this->createMock(File::class);
		/**
		 * @var MockObject|File $mini
		 */
		$mini = $this->createMock(File::class);

		$fileMap = [
			['thumb.jpg', $thumb],
			['thumb16.jpg', $mini],
		];
		$f->method('get')->willReturnMap($fileMap);

		$thumb->expects($this->exactly($thumbExists ? 1 : 0))->method('delete');
		$mini->expects($this->exactly($miniExists ? 1 : 0))->method('delete');

		$this->dut->dropThumbnails($f);
	}

	/**
	 * @dataProvider dpDrop
	 */
	public function testRecreateThumbnails($thumbExists, $miniExists) {
		/**
		 * @var MockObject|Folder $f
		 */
		$f = $this->createMock(Folder::class);
		$existMap = [
			['thumb.jpg', $thumbExists],
			['thumb16.jpg', $miniExists],
		];
		$f->method('nodeExists')->willReturnMap($existMap);

		/**
		 * @var MockObject|File $thumb
		 */
		$thumb = $this->createMock(File::class);
		/**
		 * @var MockObject|File $mini
		 */
		$mini = $this->createMock(File::class);

		$fileMap = [
			['thumb.jpg', $thumb],
			['thumb16.jpg', $mini],
		];
		$f->method('get')->willReturnMap($fileMap);
		
		$newFileMap = [
			['thumb.jpg', null, $thumb],
			['thumb16.jpg', null, $mini],
		];
		$f->method('newFile')->willReturnMap($newFileMap);

		$full = $this->createStub(File::class);
		$this->fileHelper->method('hasImage')->willReturn(true);
		$this->fileHelper->method('getImage')->willReturn($full);

		$this->generationHelper->expects($this->exactly(2))->method('generateThumbnail')
			->withConsecutive(
				[$full, ImageSize::THUMBNAIL, $thumb],
				[$full, ImageSize::MINI_THUMBNAIL, $mini],
			);

		$this->dut->recreateThumbnails($f);
	}
}
