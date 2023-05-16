<?php

namespace OCA\Cookbook\tests\unit\Helper\ImageService;

use OCA\Cookbook\Exception\RecipeImageExistsException;
use OCA\Cookbook\Helper\ImageService\ImageFileHelper;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\ImageService\ImageFileHelper
 * @covers OCA\Cookbook\Exception\RecipeImageExistsException
 */
class ImageFileHelperTest extends TestCase {
	/**
	 * @var ImageFileHelper
	 */
	private $dut;

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

		$this->folder = $this->createMock(Folder::class);

		$this->dut = new ImageFileHelper($l);
	}

	public function dpExisting() {
		yield [true];
		yield [false];
	}

	/**
	 * @dataProvider dpExisting
	 * @param mixed $present
	 */
	public function testHasImage($present) {
		$this->folder->method('nodeExists')->with('full.jpg')->willReturn($present);

		$this->assertEquals($present, $this->dut->hasImage($this->folder));
	}

	public function testGetImage() {
		$file = $this->createStub(File::class);
		$this->folder->method('get')->with('full.jpg')->willReturn($file);
		$this->assertSame($file, $this->dut->getImage($this->folder));
	}

	/**
	 * @dataProvider dpExisting
	 * @param mixed $existing
	 */
	public function testDropImage($existing) {
		/**
		 * @var MockObject|File $file
		 */
		$file = $this->createMock(File::class);
		$this->folder->method('nodeExists')->with('full.jpg')->willReturn($existing);

		if ($existing) {
			$this->folder->method('get')->with('full.jpg')->willReturn($file);
			$file->expects($this->once())->method('delete');
		} else {
			$this->folder->method('get')->with('full.jpg')->willThrowException(new NotFoundException());
			$file->expects($this->never())->method('delete');
		}

		$this->dut->dropImage($this->folder);
	}

	/**
	 * @dataProvider dpExisting
	 * @param mixed $existing
	 */
	public function testCreateImage($existing) {
		$this->folder->method('nodeExists')->with('full.jpg')->willReturn($existing);
		$file = $this->createStub(File::class);
		$this->folder->method('newFile')->with('full.jpg')->willReturn($file);

		if ($existing) {
			$this->expectException(RecipeImageExistsException::class);
		}

		$this->assertSame($file, $this->dut->createImage($this->folder));
	}
}
