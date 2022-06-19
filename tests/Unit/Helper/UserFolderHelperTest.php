<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Exception\UserFolderNotValidException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Helper\UserConfigHelper;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \OCA\Cookbook\Helper\UserFolderHelper
 * @covers \OCA\Cookbook\Exception\UserFolderNotValidException
 * @covers \OCA\Cookbook\Exception\UserFolderNotWritableException
 */
class UserFolderHelperTest extends TestCase {
	/**
	 * @var UserConfigHelper|MockObject
	 */
	private $config;
	/**
	 * @var string
	 */
	private $userId;
	/**
	 * @var IRootFolder|MockObject
	 */
	private $root;

	/**
	 * @var UserFolderHelper
	 */
	private $dut;

	/**
	 * @var ReflectionProperty
	 */
	private $cacheProp;

	protected function setUp(): void {
		parent::setup();

		$this->userId = 'test_user-id';

		$this->root = $this->createMock(IRootFolder::class);

		$l = $this->createMock(IL10N::class);
		/**
		 * @var MockObject|IL10N $l
		 */
		$l->method('t')->willReturnArgument(0);

		$this->config = $this->createMock(UserConfigHelper::class);

		$reflection = new ReflectionClass(UserFolderHelper::class);
		$this->cacheProp = $reflection->getProperty('cache');
		$this->cacheProp->setAccessible(true);

		$this->dut = new UserFolderHelper(
			$this->userId, $this->root, $l, $this->config
		);
	}

	public function testPathSetting() {
		$newPath = '/Rezepte';

		$this->config->expects($this->once())->method('setFolderName')->with($newPath);

		$this->dut->setPath($newPath);

		$this->assertNull($this->cacheProp->getValue($this->dut));
	}

	public function testGetPath() {
		$retPath = '/Recipes';
		$this->config->method('getFolderName')->willReturn($retPath);

		$this->assertEquals($retPath, $this->dut->getPath());
	}

	public function testCachedPath() {
		$folderStub = $this->createStub(Folder::class);

		$this->cacheProp->setValue($this->dut, $folderStub);

		$this->root->expects($this->never())->method('get');
		$this->root->expects($this->never())->method('newFolder');

		$this->assertSame($folderStub, $this->dut->getFolder());
	}

	public function dpExisting() {
		return [[false], [true]];
	}

	/**
	 * @dataProvider dpExisting
	 */
	public function testUncachedPath($existing) {
		/**
		 * @var Folder|Stub
		 */
		$folderStub = $this->createStub(Folder::class);
		$path = '/Recipes';
		$fullPath = "/{$this->userId}/files/Recipes";

		if ($existing) {
			$this->root->method('get')->with($fullPath)->willReturn($folderStub);
			$this->root->expects($this->never())->method('newFolder');
		} else {
			$ex = new NotFoundException();
			$this->root->method('get')->with($fullPath)->willThrowException($ex);
			$this->root->method('newFolder')->with($fullPath)->willReturn($folderStub);
		}

		$this->config->method('getFolderName')->willReturn($path);

		$folderStub->method('getType')->willReturn(FileInfo::TYPE_FOLDER);
		$folderStub->method('isCreatable')->willReturn(true);

		$this->assertSame($folderStub, $this->dut->getFolder());
	}

	public function testNoWritingPermissionGetFolder() {
		$path = '/Recipes';
		$fullPath = "/{$this->userId}/files/Recipes";

		$ex = new NotFoundException();
		$this->root->method('get')->with($fullPath)->willThrowException($ex);
		$ex1 = new NotPermittedException();
		$this->root->method('newFolder')->with($fullPath)->willThrowException($ex1);

		$this->config->method('getFolderName')->willReturn($path);

		$this->expectException(UserFolderNotValidException::class);
		$this->dut->getFolder();
	}

	public function testWrongTypeGetFolder() {
		/**
		 * @var Folder|Stub
		 */
		$nodeStub = $this->createStub(Folder::class);
		$path = '/Recipes';
		$fullPath = "/{$this->userId}/files/Recipes";

		$ex = new NotFoundException();
		$this->root->method('get')->with($fullPath)->willReturn($nodeStub);

		$this->config->method('getFolderName')->willReturn($path);

		$nodeStub->method('getType')->willReturn(FileInfo::TYPE_FILE);

		$this->expectException(UserFolderNotValidException::class);
		$this->dut->getFolder();
	}

	public function testReadOnlyGetFolder() {
		/**
		 * @var Folder|Stub
		 */
		$nodeStub = $this->createStub(Folder::class);
		$path = '/Recipes';
		$fullPath = "/{$this->userId}/files/Recipes";

		$ex = new NotFoundException();
		$this->root->method('get')->with($fullPath)->willReturn($nodeStub);

		$this->config->method('getFolderName')->willReturn($path);

		$nodeStub->method('getType')->willReturn(FileInfo::TYPE_FOLDER);
		$nodeStub->method('isCreatable')->willReturn(false);

		$this->expectException(UserFolderNotWritableException::class);
		$this->dut->getFolder();
	}
}
