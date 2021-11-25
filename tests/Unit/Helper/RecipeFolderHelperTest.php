<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCP\IL10N;
use OCP\Files\File;
use OCP\Files\Folder;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Helper\FilesystemHelper;
use OCA\Cookbook\Helper\RecipeFolderHelper;
use OCP\Files\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \OCA\Cookbook\Helper\RecipeFolderHelper
 */
class RecipeFolderHelperTest extends TestCase {

	/**
	 * @var RecipeFolderHelper
	 */
	private $dut;

	/**
	 * @var FilesystemHelper|MockObject
	 */
	private $fs;

	/**
	 * @var Folder|MockObject
	 */
	private $rf;

	protected function setUp(): void {
		/**
		 * @var MockObject|IL10N $l
		 */
		$l = $this->createMock(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->fs = $this->createMock(FilesystemHelper::class);

		$this->dut = new RecipeFolderHelper($l, $this->fs);

		$this->rf = $this->createMock(Folder::class);
	}

	/**
	 * @testWith [true]
	 * 		[false]
	 */
	public function testNodeExists($exists) {
		$name = 'recipe.json';

		$this->fs->method('nodeExists')->with($name, $this->rf)->willReturn($exists);

		$this->assertEquals($exists, $this->dut->nodeExists($this->rf, $name));
	}

	public function testGetContentExisting() {
		$name = 'recipe.json';
		$content = 'Some content in the file';

		$file = $this->createMock(File::class);

		$this->rf->expects($this->once())->method('get')->with($name)->willReturn($file);
		$file->expects($this->once())->method('getContent')->willReturn($content);

		$this->assertEquals($content, $this->dut->getContent($this->rf, $name));
	}

	public function testGetContentNonExisting() {
		$name = 'recipe.json';
		$content = 'Some content in the file';
		$ex = new NotFoundException();

		$this->rf->expects($this->once())->method('get')
			->with($name)->willThrowException($ex);

		$this->expectException(NotFoundException::class);
		$this->assertEquals($content, $this->dut->getContent($this->rf, $name));
	}

	public function testPutContent() {
		$name = 'recipe.json';
		$content = 'The content to be written to the file.';

		/**
		 * @var MockObject|File $file
		 */
		$file = $this->createMock(File::class);
		$this->fs->expects($this->once())->method('ensureFileExists')
			->with($name, $this->rf)->willReturn($file);
		$file->expects($this->once())->method('putContent')->with($content);

		$this->dut->putContent($this->rf, $name, $content);
	}

	public function testRemoveFileExisting() {
		$name = 'recipe.json';
		$this->markTestIncomplete();
	}
}
