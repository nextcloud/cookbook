<?php

namespace OCA\Cookbook\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\Cookbook\tests\Unit\Controller;

use OCA\Cookbook\Controller\Implementation\KeywordImplementation;
use OCA\Cookbook\Controller\KeywordApiController;

/**
 * @covers \OCA\Cookbook\Controller\KeywordApiController
 */
class KeywordApiControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return KeywordApiController::class;
	}

	protected function getImplementationClassName(): string {
		return KeywordImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'keywords', 'implName' => 'index'],
		];
	}
}
