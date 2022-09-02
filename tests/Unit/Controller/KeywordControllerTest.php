<?php

namespace OCA\Cookbook\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\Cookbook\tests\Unit\Controller;

use OCA\Cookbook\Controller\Implementation\KeywordImplementation;
use OCA\Cookbook\Controller\KeywordController;

/**
 * @covers \OCA\Cookbook\Controller\KeywordController
 */
class KeywordControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return KeywordController::class;
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
