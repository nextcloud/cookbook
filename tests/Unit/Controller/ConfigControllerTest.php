<?php

namespace OCA\Cookbook\tests\Unit\Controller;

use OCP\IRequest;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\RecipeService;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Helper\RestParameterParser;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Controller\ConfigController;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use ReflectionProperty;

/**
 * @coversDefaultClass OCA\Cookbook\Controller\ConfigController
 * @covers ::<private>
 * @covers ::<protected>
 */
class ConfigControllerTest extends TestCase {
	
	/**
	 * @var ConfigController|MockObject
	 */
	private $sut;
	/**
	 * @var RecipeService|MockObject
	 */
	private $recipeService;
	/**
	 * @var DbCacheService|MockObject
	 */
	private $dbCacheService;
	/**
	 * @var RestParameterParser|MockObject
	 */
	private $restParser;
	/**
	 * @var IRequest|MockObject
	 */
	private $request;

	public function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->recipeService = $this->createMock(RecipeService::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->restParser = $this->createMock(RestParameterParser::class);

		$this->sut = new ConfigController('cookbook', $this->request, $this->recipeService, $this->dbCacheService, $this->restParser);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructor(): void {
		$this->ensurePropertyIsCorrect('service', $this->recipeService);
		$this->ensurePropertyIsCorrect('dbCacheService', $this->dbCacheService);
		$this->ensurePropertyIsCorrect('restParser', $this->restParser);
	}

	private function ensurePropertyIsCorrect(string $name, &$val) {
		$property = new ReflectionProperty(ConfigController::class, $name);
		$property->setAccessible(true);
		$this->assertSame($val, $property->getValue($this->sut));
	}

	/**
	 * @covers ::reindex
	 */
	public function testReindex(): void {
		$this->dbCacheService->expects($this->once())->method('updateCache');

		/**
		 * @var Response $response
		 */
		$response = $this->sut->reindex();

		$this->assertEquals(200, $response->getStatus());
	}

	/**
	 * @covers ::list
	 */
	public function testList(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');

		$folder = '/the/folder/to/check';
		$interval = 5*60;
		$printImage = true;

		$expectedData = [
			'folder' => $folder,
			'update_interval' => $interval,
			'print_image' => $printImage,
		];

		$this->recipeService->method('getUserFolderPath')->willReturn($folder);
		$this->dbCacheService->method('getSearchIndexUpdateInterval')->willReturn($interval);
		$this->recipeService->method('getPrintImage')->willReturn($printImage);

		/**
		 * @var DataResponse $response
		 */
		$response = $this->sut->list();

		$this->assertEquals(200, $response->getStatus());
		$this->assertEquals($expectedData, $response->getData());
	}

	/**
	 * @dataProvider dataProviderConfig
	 * @covers ::config
	 */
	public function testConfig($data, $folderPath, $interval, $printImage): void {
		$this->restParser->method('getParameters')->willReturn($data);

		$this->dbCacheService->expects($this->once())->method('triggerCheck');

		if(is_null($folderPath)){
			$this->recipeService->expects($this->never())->method('setUserFolderPath');
			$this->dbCacheService->expects($this->never())->method('updateCache');
		} else {
			$this->recipeService->expects($this->once())->method('setUserFolderPath')->with($folderPath);
			$this->dbCacheService->expects($this->once())->method('updateCache');
		}

		if(is_null($interval)){
			$this->recipeService->expects($this->never())->method('setSearchIndexUpdateInterval');
		} else {
			$this->recipeService->expects($this->once())->method('setSearchIndexUpdateInterval')->with($interval);
		}

		if(is_null($printImage)) {
			$this->recipeService->expects($this->never())->method('setPrintImage');
		} else {
			$this->recipeService->expects($this->once())->method('setPrintImage')->with($printImage);
		}

		/**
		 * @var DataResponse $response
		 */
		$response = $this->sut->config();

		$this->assertEquals(200, $response->getStatus());
	}

	public function dataProviderConfig() {
		return [
			'noChange' => [
				[], null, null, null
			],
			'changeFolder' => [
				['folder' => '/path/to/whatever'], '/path/to/whatever', null, null
			],
			'changeinterval' => [
				['update_interval' => 15], null, 15, null
			],
			'changePrint' => [
				['print_image' => true], null, null, true
			],
			'changeAll' => [
				[
					'folder' => '/my/custom/path',
					'update_interval' => 12,
					'print_image' => false
				], '/my/custom/path', 12, false
			],
		];
	}

}
