<?php

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Exception\InvalidThumbnailTypeException;
use OCA\Cookbook\Exception\RecipeImageExistsException;
use OCA\Cookbook\Helper\FilesystemHelper;
use OCP\Files\File;
use OCP\Files\Folder;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\ImageService;
use OCA\Cookbook\Service\ThumbnailService;
use OCP\Files\NotFoundException;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

/**
 * @covers \OCA\Cookbook\Service\ImageService
 */
class ImageServiceTest extends TestCase {

	/**
	 * @var ThumbnailService|MockObject
	 */
	private $thumbnailService;

	/**
	 * @var IL10N|MockObject
	 */
	private $l;

	/**
	 * @var FilesystemHelper|MockObject
	 */
	private $fs;

	/**
	 * @var ImageService|MockObject
	 */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$this->thumbnailService = $this->createMock(ThumbnailService::class);
		$this->l = $this->createMock(IL10N::class);
		$this->fs = $this->createMock(FilesystemHelper::class);

		$this->l->method('t')->willReturnArgument(0);

		$this->dut = $this->getMockBuilder(ImageService::class)
			->onlyMethods(['generateThumb'])
			->enableOriginalConstructor()
			->setConstructorArgs([$this->thumbnailService, $this->fs, $this->l])
			->getMock();
		// $this->dut = new ImageService($this->thumbnailService, $this->fs, $this->l);
	}

	public function dpGetImage() {
		yield [ true ];
		yield [ false ];
	}

	/**
	 * @dataProvider dpGetImage
	 */
	public function testGetImage(bool $imageFound) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);
		$img = $this->createStub(File::class);

		$getCall = $folder->expects($this->once())->method('get')->with('full.jpg');
		if ($imageFound) {
			$getCall->willReturn($img);
		} else {
			$getCall->willThrowException(new NotFoundException());
		}

		try {
			$ret = $this->dut->getImage($folder);
	
			$this->assertSame($img, $ret);
		} catch (NotFoundException $ex) {
			$this->assertFalse($imageFound);
		}
	}

	public function dpTypesValid() {
		return [
			[ImageService::THUMB_MAIN, 'thumb.jpg'],
			[ImageService::THUMB_16, 'thumb16.jpg'],
		];
	}
	public function dpTypesInvalid() {
		return [
			[0], [5], [16]
		];
	}

	private function ensureThumbnailExists(
		string $fileName,
		bool $fileExists,
		int $type
	) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);
		/**
		 * @var MockObject|File $img
		 */
		$img = $this->createMock(File::class);

		$this->fs->expects($this->once())->method('ensureFileExists')
			->with($fileName, $folder)
			->willReturn($img);
		
		$size = $fileExists ? 10 : 0;
		$img->expects($this->once())->method('getSize')->willReturn($size);

		if ($fileExists) {
			$this->dut->expects($this->never())->method('generateThumb');
		// $img->expects($this->once())->method('putContent');
		} else {
			$this->dut->expects($this->once())->method('generateThumb')->with($folder, $type, $img);
			// $img->expects($this->never())->method('putContent');
		}

		$res = $this->dut->ensureThumbnailExists($folder, $type);

		$this->assertSame($img, $res);
	}

	/**
	 * @dataProvider dpTypesValid
	 */
	public function testGetThumbnailDirect($type, $fileName) {
		// /**
		//  * @var MockObject|Folder $folder
		//  */
		// $folder = $this->createMock(Folder::class);
		// /**
		//  * @var MockObject|File $img
		//  */
		// $img = $this->createMock(File::class);

		// $this->fs->expects($this->once())->method('ensureFileExists')
		// 	->with($fileName, $folder)
		// 	->willReturn($img);
		
		// $img->expects($this->once())->method('getSize')->willReturn(10);
		// $img->expects($this->never())->method('putContent');

		// $res = $this->dut->ensureThumbnailExists($folder, $type);

		// $this->assertSame($img, $res);
		$this->ensureThumbnailExists($fileName, true, $type);
	}

	/**
	 * @dataProvider dpTypesValid
	 */
	public function testGetThumbnailNonExisting($type, $fileName) {
		$this->ensureThumbnailExists($fileName, false, $type);
	}

	/**
	 * @dataProvider dpTypesInvalid
	 * @group disabled
	 */
	public function testGetThumbnailsInvalid($type) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);

		$folder->expects($this->never())->method('get');

		$this->expectException(InvalidThumbnailTypeException::class);
		$this->dut->getThumbnail($folder, $type);
	}

	/**
	 * @dataProvider dpTypesValid
	 * @group disabled
	 */
	public function testGetThumbnailCreate($type, $fileName) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);
		/**
		 * @var MockObject|File $img
		 */
		$img = $this->createMock(File::class);

		/**
		 * @var Stub $fullImg
		 */
		$fullImg = $this->createStub(File::class);
		$fullContent = 'The content of the full scaled image';
		$fullImg->method('getContent')->willReturn($fullContent);
		$thumbContent = 'The thumbnail';

		switch ($type) {
			case ImageService::THUMB_MAIN:
				$methodName = 'getThumbnailMainSize';
				break;
			case ImageService::THUMB_16:
				$methodName = 'getThumbnailMiniSize';
				break;
			default:
				$this->assertFalse(true, 'Bad test.');
				return;
		}

		$countMatcher = $this->exactly(2);
		$folder->expects($countMatcher)
			->method('get')
			->withConsecutive([$fileName], ['full.jpg'])
			->willReturnCallback(function ($p) use ($countMatcher, $fullImg) {
				if ($countMatcher->getInvocationCount() === 1) {
					throw new NotFoundException();
				} else {
					return $fullImg;
				}
			});
		$this->thumbnailService->expects($this->once())
			->method($methodName)
			->with($fullContent)
			->willReturn($thumbContent);
		$folder->expects($this->once())->method('newFile')->with($fileName)->willReturn($img);
		$img->expects($this->once())->method('putContent')->with($thumbContent);

		$res = $this->dut->getThumbnail($folder, $type);

		$this->assertSame($img, $res);
	}

	/**
	 * @dataProvider dpTypesValid
	 * @group disabled
	 */
	public function testGetThumbnailCreateNoImage($type, $fileName) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);
		/**
		 * @var MockObject|File $img
		 */
		$img = $this->createMock(File::class);

		switch ($type) {
			case ImageService::THUMB_MAIN:
				$methodName = 'getThumbnailMainSize';
				break;
			case ImageService::THUMB_16:
				$methodName = 'getThumbnailMiniSize';
				break;
			default:
				$this->assertFalse(true, 'Bad test.');
				return;
		}

		$folder->expects($this->exactly(2))
			->method('get')
			->withConsecutive([$fileName], ['full.jpg'])
			->willThrowException(new NotFoundException());

		$this->expectException(NotFoundException::class);
		$res = $this->dut->getThumbnail($folder, $type);
	}

	public function dpCreateImage() {
		return [[true], [false]];
	}

	/**
	 * @dataProvider dpCreateImage
	 * @group disabled
	 */
	public function testCreateImage($exists) {
		//
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);
		$img = $this->createStub(File::class);

		$folder->expects($this->once())->method('nodeExists')->with('full.jpg')->willReturn($exists);
		$folder->method('get')->with('full.jpg')->willReturn($img);

		try {
			$this->assertSame($img, $this->dut->createImage($folder));
		} catch (RecipeImageExistsException $ex) {
			$this->assertTrue($exists);
		}
	}

	public function dpRecreateThumbs() {
		return [
			[false, false],
			[false, true],
			[true, false],
			[true, true],
		];
	}

	/**
	 * @dataProvider dpRecreateThumbs
	 * @group disabled
	 */
	public function testRecreateThumbs($thumbExists, $miniExists) {
		/**
		 * @var MockObject|Folder $folder
		 */
		$folder = $this->createMock(Folder::class);

		$fullContent = 'This is the complete content of the image';
		/**
		 * @var File|Stub $fullImg
		 */
		$fullImg = $this->createStub(File::class);
		$fullImg->method('getContent')->willReturn($fullContent);

		$thumbContent = 'The thumb content';
		$miniContent = 'mini content';
		$this->thumbnailService->method('getThumbnailMainSize')->with($fullContent)->willReturn($thumbContent);
		$this->thumbnailService->method('getThumbnailMiniSize')->with($thumbContent)->willReturn($miniContent);
		$thumbFile = $this->createStub(File::class);
		$miniFile = $this->createStub(File::class);

		$cnt = 1;
		$arg = [['full.jpg']];
		$newArgs = [];
		if ($thumbExists) {
			$cnt ++;
			$arg[] = ['thumb.jpg'];
		} else {
			$newArgs[] = ['thumb.jpg'];
		}
		if ($miniExists) {
			$cnt ++;
			$arg[] = ['thumb16.jpg'];
		} else {
			$newArgs[] = ['thumb16.jpg'];
		}
		$folder->expects($this->exactly($cnt))
			->method('get')
			->withConsecutive(...$arg)
			->willReturnOnConsecutiveCalls($fullImg, $thumbFile, $miniFile);
		
		$folder->expects($this->exactly(2))->method('nodeExists')
			->withConsecutive(['thumb.jpg'], ['thumb16.jpg'])
			->willReturnOnConsecutiveCalls($thumbExists, $miniExists);
		$folder->expects($this->exactly(3 - $cnt))->method('newFile')
			->withConsecutive(...$newArgs)->willReturnOnConsecutiveCalls($thumbFile, $miniFile);

		$this->dut->recreateThumbnails($folder);
	}

	/**
	 * @testWith [true]
	 * 				[false]
	 */
	public function testHasImage($exists) {
		/**
		 * @var Folder $recipeFolder
		 */
		$recipeFolder = $this->createStub(Folder::class);
		$this->fs->method('nodeExists')->with('full.jpg', $recipeFolder)->willReturn($exists);
		$this->assertEquals($exists, $this->dut->hasImage($recipeFolder));
	}
}
