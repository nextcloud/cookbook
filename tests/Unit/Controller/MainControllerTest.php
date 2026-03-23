<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use OCA\Cookbook\Controller\MainController;
use OCA\Cookbook\Exception\FolderNotWritableException;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Helper\MyRecipesFolderHelper;
use OCA\Cookbook\Helper\SharedRecipesFolderHelper;
use OCA\Cookbook\Service\DbCacheService;
use OCP\Files\Folder;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cookbook\Controller\MainController
 * @covers \OCA\Cookbook\Exception\FolderNotWritableException
 */
class MainControllerTest extends TestCase {
	/**
	 * @var DbCacheService|MockObject
	 */
	private $dbCacheService;
	/**
	 * @var UserFolderHelper|MockObject
	 */
	private $userFolder;
	/**
	 * @var MyRecipesFolderHelper|MockObject
	 */
	private $myRecipesFolder;
	/**
	 * @var SharedRecipesFolderHelper|MockObject
	 */
	private $sharedRecipesFolder;

	/**
	 * @var MainController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$request = $this->createStub(IRequest::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->userFolder = $this->createMock(UserFolderHelper::class);
		$this->myRecipesFolder = $this->createMock(MyRecipesFolderHelper::class);
		$this->sharedRecipesFolder = $this->createMock(SharedRecipesFolderHelper::class);

		$this->sut = new MainController(
			'cookbook',
			$request,
			$this->dbCacheService,
			$this->userFolder,
			$this->myRecipesFolder,
			$this->sharedRecipesFolder
		);
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}

	public function testIndex(): void {
		$this->ensureCacheCheckTriggered();
		$userFolder = $this->createStub(Folder::class);
		$myRecipesFolder = $this->createStub(Folder::class);
		$sharedRecipesFolder = $this->createStub(Folder::class);
		$this->userFolder->method('getFolder')->willReturn($userFolder);
		$this->myRecipesFolder->method('getFolder')->willReturn($myRecipesFolder);
		$this->sharedRecipesFolder->method('getFolder')->willReturn($sharedRecipesFolder);

		$ret = $this->sut->index();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('index', $ret->getTemplateName());
	}

	public function testIndexInvalidUser(): void {
		$this->userFolder->method('getFolder')->willThrowException(new FolderNotWritableException());
		$this->myRecipesFolder->method('getFolder')->willThrowException(new FolderNotWritableException());
		$this->sharedRecipesFolder->method('getFolder')->willThrowException(new FolderNotWritableException());
		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('invalid_guest', $ret->getTemplateName());
	}
}
