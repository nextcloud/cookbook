<?php

namespace OCA\Cookbook\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\Cookbook\tests\Unit\Controller;

use OCP\IRequest;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use OCP\AppFramework\Http\Response;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Helper\RestParameterParser;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Controller\ConfigController;
use OCA\Cookbook\Controller\ConfigApiController;
use OCA\Cookbook\Controller\Implementation\ConfigImplementation;

/**
 * @covers OCA\Cookbook\Controller\ConfigApiController
 */
class ConfigApiControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return ConfigApiController::class;
	}

	protected function getImplementationClassName(): string {
		return ConfigImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'list'],
			['name' => 'reindex'],
			['name' => 'config', 'once' => true],
		];
	}
}
