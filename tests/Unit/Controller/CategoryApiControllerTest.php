<?php

namespace OCA\Cookbook\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\Cookbook\tests\Unit\Controller;

use OCA\Cookbook\Controller\CategoryApiController;
use OCA\Cookbook\Controller\Implementation\CategoryImplementation;

/**
 * @covers \OCA\Cookbook\Controller\CategoryApiController
 */
class CategoryApiControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return CategoryApiController::class;
	}

	protected function getImplementationClassName(): string {
		return CategoryImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'categories', 'implName' => 'index'],
			['name' => 'rename', 'args' => [['my category']], 'once' => true],
		];
	}
}
