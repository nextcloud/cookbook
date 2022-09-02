<?php

namespace OCA\Cookbook\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\Cookbook\tests\Unit\Controller;

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
