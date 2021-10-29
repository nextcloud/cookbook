<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Exception\UserFolderNotValidException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Exception\WrongFileTypeException;
use OCA\Cookbook\Helper\FilesystemHelper;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCP\Files\Folder;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \OCA\Cookbook\Helper\UserFolderHelper
 * @covers \OCA\Cookbook\Exception\UserFolderNotValidException
 * @covers \OCA\Cookbook\Exception\UserFolderNotWritableException
 */
class UserFolderHelperTest extends TestCase {

	/**
	 * @var IConfig|MockObject
	 */
	private $config;
	/**
	 * @var IL10N|MockObject
	 */
	private $l;
	/**
	 * @var string
	 */
	private $userId;

	/**
	 * @var MockObject|FilesystemHelper
	 */
	private $filesystem;

	/**
	 * @var UserFolderHelper
	 */
	private $dut;

	protected function setUp(): void {
		parent::setup();

		$this->config = $this->createMock(IConfig::class);
		$this->l = $this->createMock(IL10N::class);
		$this->l->method('t')->willReturnArgument(0);
		$this->userId = 'test_user-id';
		$this->filesystem = $this->createMock(FilesystemHelper::class);

		$this->dut = new UserFolderHelper(
			$this->userId, $this->config, $this->l, $this->filesystem
		);
	}

	public function testConstructor() {
		$reflection = new ReflectionClass(UserFolderHelper::class);
		$propConfig = $reflection->getProperty('config');
		$propConfig->setAccessible(true);
		$propL = $reflection->getProperty('l');
		$propL->setAccessible(true);
		$propUserId = $reflection->getProperty('userId');
		$propUserId->setAccessible(true);
		$propFilesystem = $reflection->getProperty('filesystem');
		$propFilesystem->setAccessible(true);

		$this->assertSame($this->config, $propConfig->getValue($this->dut));
		$this->assertSame($this->l, $propL->getValue($this->dut));
		$this->assertSame($this->userId, $propUserId->getValue($this->dut));
		$this->assertSame($this->filesystem, $propFilesystem->getValue($this->dut));
	}

	public function testPathSetting() {
		$oldPath = '/Recipes';
		$newPath = '/Rezepte';

		$oldAbsPath = "/{$this->userId}/files$oldPath";
		$newAbsPath = "/{$this->userId}/files$newPath";

		$oldFolder = $this->createStub(Folder::class);
		$newFolder = $this->createStub(Folder::class);

		$this->config->expects($this->exactly(2))->method('getUserValue')
			->withConsecutive(
				[$this->userId, 'cookbook', 'folder'],
				[$this->userId, 'cookbook', 'folder'],
			)->willReturnOnConsecutiveCalls($oldPath, $newPath);
		$this->config->expects($this->once())->method('setUserValue')->with(
			$this->userId, 'cookbook', 'folder', $newPath
		);

		$this->filesystem->expects($this->exactly(2))->method('ensureFolderExists')
			->withConsecutive([$oldAbsPath], [$newAbsPath])
			->willReturnOnConsecutiveCalls($oldFolder, $newFolder);
		$this->filesystem->method('folderHasFullPermissions')->willReturn(true);
		
		$this->assertSame($oldFolder, $this->dut->getFolder());
		$this->assertSame($oldFolder, $this->dut->getFolder());
		$this->dut->setPath($newPath);
		$this->assertSame($newFolder, $this->dut->getFolder());
	}

	public function testCachedPath() {
		$folderStub = $this->createStub(Folder::class);

		$this->config->method('getUserValue')->with(
			$this->userId, 'cookbook', 'folder'
		)->willReturn('/Recipes');
		$this->config->expects($this->never())->method('setUserValue');

		$this->filesystem->expects($this->once())
			->method('ensureFolderExists')
			->with("/{$this->userId}/files/Recipes")
			->willReturn($folderStub);
		$this->filesystem->method('folderHasFullPermissions')->willReturn(true);
		
		$ret = $this->dut->getFolder();
		$this->assertSame($folderStub, $ret);
		
		// Second query for check of caching
		$ret = $this->dut->getFolder();
		$this->assertSame($folderStub, $ret);
	}

	public function testDefaultPathName() {
		$expectedPath = "/Recipes";

		$this->config->expects($this->once())->method('getUserValue')->with(
			$this->userId,
			'cookbook',
			'folder'
		)->willReturn(null);
		$this->config->expects($this->once())->method('setUserValue')->with(
			$this->userId, 'cookbook', 'folder', $expectedPath
		);

		$path = $this->dut->getPath();
		$this->assertEquals($expectedPath, $path);
	}

	public function dpExceptions() {
		return [
			'wrongFileType' => [true, false, false, UserFolderNotValidException::class],
			'notPermitted' => [false, true, false, UserFolderNotValidException::class],
			'fullPermissions' => [false, false, true, UserFolderNotWritableException::class],
		];
	}

	/**
	 * @dataProvider dpExceptions
	 */
	public function testExceptions($wrongFileType, $notPermitted, $fullPermissions, $exClass) {
		$this->config->method('getUserValue')->willReturn(null);

		$ensureCall = $this->filesystem->expects($this->once())->method('ensureFolderExists');

		if ($wrongFileType) {
			$ensureCall->willThrowException(new WrongFileTypeException());
		} elseif ($notPermitted) {
			$ensureCall->willThrowException(new NotPermittedException());
		} elseif ($fullPermissions) {
			$stub = $this->createStub(Folder::class);
			$ensureCall->willReturn($stub);
			$this->filesystem->expects($this->once())->method('folderHasFullPermissions')->with($stub)
				->willReturn(false);
		} else {
			$this->assertFalse(true, 'Bad test case.');
			return;
		}

		try {
			$this->dut->getFolder();
		} catch (UserFolderNotValidException $ex) {
			$this->assertEquals(UserFolderNotValidException::class, $exClass);
		} catch (UserFolderNotWritableException $ex) {
			$this->assertEquals(UserFolderNotWritableException::class, $exClass);
		}

		// $this->markTestIncomplete();
	}
}
