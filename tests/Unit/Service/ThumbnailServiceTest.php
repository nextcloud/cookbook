<?php

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Service\ThumbnailService;
use OCP\Image;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers OCA\Cookbook\Service\ThumbnailService
 */
class ThumbnailServiceTest extends TestCase {

	/**
	 * @var ThumbnailService|MockObject
	 */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$this->dut = $this->getMockBuilder(ThumbnailService::class)
			->onlyMethods(['getNewImage'])
			->enableOriginalConstructor()
			->setConstructorArgs([])
			->getMock()
			;
	}

	public function testMainSize() {
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
		$image->expects($this->once())->method('resize')->with(256);
		$image->expects($this->once())->method('centerCrop');
		$image->method('data')->willReturn($newData);

		$this->assertEquals($newData, $this->dut->getThumbnailMainSize($data));
	}

	public function testMiniSize() {
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
		$image->expects($this->once())->method('resize')->with(16);
		$image->expects($this->once())->method('centerCrop');
		$image->method('data')->willReturn($newData);

		$this->assertEquals($newData, $this->dut->getThumbnailMiniSize($data));
	}

	public function testGetImage() {
		$dut = new ThumbnailService();

		$cls = new ReflectionClass(ThumbnailService::class);
		$method = $cls->getMethod('getNewImage');
		$method->setAccessible(true);

		$img = $method->invoke($dut);

		$this->assertTrue($img instanceof Image);
		$this->assertEquals('', $img->data());
	}
}
