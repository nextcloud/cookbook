<?php

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Exception\InvalidThumbnailTypeException;
use OCA\Cookbook\Helper\ImageService\ImageSize;
use OCA\Cookbook\Service\ThumbnailService;
use OCP\IL10N;
use OCP\Image;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers OCA\Cookbook\Service\ThumbnailService
 * @covers OCA\Cookbook\Exception\InvalidThumbnailTypeException
 */
class ThumbnailServiceTest extends TestCase {
	/**
	 * @var ThumbnailService|MockObject
	 */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		/**
		 * @var Stub|IL10N $l
		 */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->dut = $this->getMockBuilder(ThumbnailService::class)
			->onlyMethods(['getNewImage'])
			->enableOriginalConstructor()
			->setConstructorArgs([$l])
			->getMock()
			;
	}

	public function testGetImage() {
		$l = $this->createStub(IL10N::class);
		$dut = new ThumbnailService($l);

		$cls = new ReflectionClass(ThumbnailService::class);
		$method = $cls->getMethod('getNewImage');
		$method->setAccessible(true);

		$img = $method->invoke($dut);

		$this->assertTrue($img instanceof Image);
		$this->assertEquals('', $img->data());
	}

	public function dpTypes() {
		yield [ImageSize::THUMBNAIL, 256];
		yield [ImageSize::MINI_THUMBNAIL, 16];
	}

	/**
	 * @dataProvider dpTypes
	 * @param mixed $type
	 * @param mixed $size
	 */
	public function testgetThumbnail($type, $size) {
		/**
		 * @var MockObject|Image
		 */
		$image = $this->createMock(Image::class);
		$data = 'original Image Byte Code';
		$newData = 'Simplified Image Data';

		$this->dut->expects($this->once())->method('getNewImage')->willReturn($image);

		$image->expects($this->once())->method('loadFromFile')
			->with($this->callback(function ($p) use ($data) {
				$dataRead = file_get_contents($p);
				return $data === $dataRead;
			}));
		$image->expects($this->once())->method('fixOrientation');
		$image->expects($this->once())->method('resize')->with($size);
		$image->expects($this->once())->method('centerCrop');
		$image->method('data')->willReturn($newData);

		$this->assertEquals($newData, $this->dut->getThumbnail($data, $type));
	}

	public function testGetThumbnailFullImage() {
		$this->expectException(InvalidThumbnailTypeException::class);
		$this->dut->getThumbnail('', ImageSize::PRIMARY_IMAGE);
	}

	public function dpInvalidTypes() {
		return [
			[-1],
			[10],
		];
	}

	/**
	 * @dataProvider dpInvalidTypes
	 * @param mixed $type
	 */
	public function testGetThumbnailInvalidType($type) {
		$this->expectException(InvalidThumbnailTypeException::class);
		$this->dut->getThumbnail('', $type);
	}
}
