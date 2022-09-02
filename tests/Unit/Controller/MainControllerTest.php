<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use Exception;
use OCP\IRequest;
use OCP\Files\File;
use OCP\IURLGenerator;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Controller\MainController;
use OCA\Cookbook\Helper\RestParameterParser;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCP\Files\Folder;

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
