<?php

namespace OCA\Cookbook\tests\Unit\Controller\Implementation;

use OCA\Cookbook\Controller\Implementation\ConfigImplementation;
use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers OCA\Cookbook\Controller\Implementation\ConfigImplementation
 */
class ConfigImplementationTest extends TestCase {
	/**
	 * @var ConfigImplementation|MockObject
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
	 * @var UserFolderHelper|MockObject
	 */
	private $userFolder;

	public function setUp(): void {
		parent::setUp();

		$this->recipeService = $this->createMock(RecipeService::class);
		$this->dbCacheService = $this->createMock(DbCacheService::class);
		$this->restParser = $this->createMock(RestParameterParser::class);
		$this->userFolder = $this->createMock(UserFolderHelper::class);

		$this->sut = new ConfigImplementation(
			$this->recipeService,
			$this->dbCacheService,
			$this->restParser,
			$this->userFolder
		);
	}

	public function testConstructor(): void {
		$this->ensurePropertyIsCorrect('service', $this->recipeService);
		$this->ensurePropertyIsCorrect('dbCacheService', $this->dbCacheService);
		$this->ensurePropertyIsCorrect('restParser', $this->restParser);
	}

	private function ensurePropertyIsCorrect(string $name, &$val) {
		$property = new ReflectionProperty(ConfigImplementation::class, $name);
		$property->setAccessible(true);
		$this->assertSame($val, $property->getValue($this->sut));
	}

	public function testReindex(): void {
		$this->dbCacheService->expects($this->once())->method('updateCache');

		/**
		 * @var Response $response
		 */
		$response = $this->sut->reindex();

		$this->assertEquals(200, $response->getStatus());
	}

	public function testList(): void {
		$this->dbCacheService->expects($this->once())->method('triggerCheck');

		$folder = '/the/folder/to/check';
		$interval = 5 * 60;
		$printImage = true;

		$expectedData = [
			'folder' => $folder,
			'update_interval' => $interval,
			'print_image' => $printImage,
			'visibleInfoBlocks' => array(),
		];

		$this->userFolder->method('getPath')->willReturn($folder);
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
	 * @param mixed $data
	 * @param mixed $folderPath
	 * @param mixed $interval
	 * @param mixed $printImage
	 * @param mixed $visibleInfoBlocks
	 */
	public function testConfig($data, $folderPath, $interval, $printImage, $visibleInfoBlocks): void {
		$this->restParser->method('getParameters')->willReturn($data);

		$this->dbCacheService->expects($this->once())->method('triggerCheck');

		if (is_null($folderPath)) {
			$this->userFolder->expects($this->never())->method('setPath');
			$this->dbCacheService->expects($this->never())->method('updateCache');
		} else {
			$this->userFolder->expects($this->once())->method('setPath')->with($folderPath);
			$this->dbCacheService->expects($this->once())->method('updateCache');
		}

		if (is_null($interval)) {
			$this->recipeService->expects($this->never())->method('setSearchIndexUpdateInterval');
		} else {
			$this->recipeService->expects($this->once())->method('setSearchIndexUpdateInterval')->with($interval);
		}

		if (is_null($printImage)) {
			$this->recipeService->expects($this->never())->method('setPrintImage');
		} else {
			$this->recipeService->expects($this->once())->method('setPrintImage')->with($printImage);
		}

		if (is_null($visibleInfoBlocks)) {
			$this->recipeService->expects($this->never())->method('setVisibleInfoBlocks');
		} else {
			$this->recipeService->expects($this->once())->method('setVisibleInfoBlocks')->with($visibleInfoBlocks);
		}

		/**
		 * @var JSONResponse $response
		 */
		$response = $this->sut->config();

		$this->assertEquals(200, $response->getStatus());
	}

	public function dataProviderConfig() {
		return [
			'noChange' => [
				[], null, null, null, null
			],
			'changeFolder' => [
				['folder' => '/path/to/whatever'], '/path/to/whatever', null, null, null
			],
			'changeinterval' => [
				['update_interval' => 15], null, 15, null, null
			],
			'changePrint' => [
				['print_image' => true], null, null, true, null
			],
			'changeVisibleBlocks' => [
				['visibleInfoBlocks' => ['cooking-time' => true, 'preparation-time' => true]],
				null, null, null, ['cooking-time' => true, 'preparation-time' => true]
			],
			'changeAll' => [
				[
					'folder' => '/my/custom/path',
					'update_interval' => 12,
					'print_image' => false,
					'visibleInfoBlocks' => ['cooking-time' => true, 'preparation-time' => true],
				], '/my/custom/path', 12, false, ['cooking-time' => true, 'preparation-time' => true]
			],
		];
	}
}
