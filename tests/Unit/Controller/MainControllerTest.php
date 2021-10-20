<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\RecipeService;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Controller\MainController;
use OCA\Cookbook\Helper\RestParameterParser;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Exception\UserFolderNotWritableException;

/**
 * @coversDefaultClass \OCA\Cookbook\Controller\MainController
 * @covers ::<private>
 * @covers ::<protected>
 */
class MainControllerTest extends TestCase {
	
	/**
	 * @var MockObject|RecipeService
	 */
	private $recipeService;
	/**
	 * @var IURLGenerator|MockObject
	 */
	private $urlGenerator;
	/**
	 * @var DbCacheService|MockObject
	 */
	private $dbCacheService;
	/**
	 * @var RestParameterParser|MockObject
	 */
	private $restParser;

	/**
	 * @var MainController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$this->recipeService = $this->createMock(RecipeService::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->restParser = $this->createMock(RestParameterParser::class);
		$request = $this->createStub(IRequest::class);

		$this->sut = new MainController('cookbook', $request, $this->recipeService, $this->dbCacheService);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructor(): void {
		$this->ensurePropertyIsCorrect('service', $this->recipeService);
		$this->ensurePropertyIsCorrect('dbCacheService', $this->dbCacheService);
	}

	private function ensurePropertyIsCorrect(string $name, &$val) {
		$property = new ReflectionProperty(MainController::class, $name);
		$property->setAccessible(true);
		$this->assertSame($val, $property->getValue($this->sut));
	}

	private function ensureCacheCheckTriggered(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');
	}

	/**
	 * @covers ::index
	 */
	public function testIndex(): void {
		$this->ensureCacheCheckTriggered();
		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('index', $ret->getTemplateName());
	}

	/**
	 * @covers ::index
	 */
	public function testIndexInvalidUser(): void {
		$this->recipeService->method('getFolderForUser')->willThrowException(new UserFolderNotWritableException());
		$ret = $this->sut->index();
		$this->assertEquals(200, $ret->getStatus());
		$this->assertEquals('invalid_guest', $ret->getTemplateName());
	}

	/**
	 * @covers ::getApiVersion
	 */
	public function testGetAPIVersion(): void {
		$ret = $this->sut->getApiVersion();
		$this->assertEquals(200, $ret->getStatus());

		$retData = $ret->getData();
		$this->assertTrue(isset($retData['cookbook_version']));
		$this->assertEquals(3, count($retData['cookbook_version']));
		$this->assertTrue(isset($retData['api_version']));
		$this->assertTrue(isset($retData['api_version']['epoch']));
		$this->assertTrue(isset($retData['api_version']['major']));
		$this->assertTrue(isset($retData['api_version']['minor']));
	}
}
