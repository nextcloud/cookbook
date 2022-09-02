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
use OCA\Cookbook\Controller\UtilApiController;
use OCA\Cookbook\Helper\RestParameterParser;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Helper\UserFolderHelper;

/**
 * @covers \OCA\Cookbook\Controller\UtilApiController
 * @covers \OCA\Cookbook\Exception\UserFolderNotWritableException
 */
class UtilApiControllerTest extends TestCase {

	/**
	 * @var UtilApiController
	 */
	private $sut;

	public function setUp(): void {
		parent::setUp();

		$request = $this->createStub(IRequest::class);

		$this->sut = new UtilApiController(
			'cookbook',
			$request
		);
	}

	public function testGetAPIVersion(): void {
		$ret = $this->sut->getApiVersion();

		$this->assertEquals(200, $ret->getStatus());

		$retData = $ret->getData();
		$this->assertArrayHasKey('cookbook_version', $retData);
		$this->assertGreaterThanOrEqual(3, count($retData['cookbook_version']));
		$this->assertLessThanOrEqual(4, count($retData['cookbook_version']));
		$this->assertIsInt($retData['cookbook_version'][0]);
		$this->assertIsInt($retData['cookbook_version'][1]);
		$this->assertIsInt($retData['cookbook_version'][2]);
		if(count($retData['cookbook_version']) === 4) {
			$this->assertIsString($retData['cookbook_version'][3]);
		}

		$this->assertArrayHasKey('api_version', $retData);
		$this->assertArrayHasKey('epoch', $retData['api_version']);
		$this->assertArrayHasKey('major', $retData['api_version']);
		$this->assertArrayHasKey('minor', $retData['api_version']);
	}



}
