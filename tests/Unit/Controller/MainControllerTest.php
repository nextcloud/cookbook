<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use OCA\Cookbook\Controller\MainController;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Service\DbCacheService;
use OCP\Files\Folder;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cookbook\Controller\MainController
 * @covers \OCA\Cookbook\Exception\UserFolderNotWritableException
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
	 * @var MainController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$request = $this->createStub(IRequest::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->userFolder = $this->createMock(UserFolderHelper::class);

		$this->sut = new MainController(
			'cookbook',
			$request,
			$this->dbCacheService,
			$this->userFolder
		);
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}

	public function testIndex(): void {
		$this->ensureCacheCheckTriggered();
		$userFolder = $this->createStub(Folder::class);
		$this->userFolder->method('getFolder')->willReturn($userFolder);

		$ret = $this->sut->index();

		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('index', $ret->getTemplateName());
	}

	public function testIndexInvalidUser(): void {
		$this->userFolder->method('getFolder')->willThrowException(new UserFolderNotWritableException());
		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('invalid_guest', $ret->getTemplateName());
	}
}
