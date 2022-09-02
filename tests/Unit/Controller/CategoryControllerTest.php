<?php

namespace OCA\Cookbook\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

namespace OCA\Cookbook\tests\Unit\Controller;

use OCA\Cookbook\Controller\CategoryController;
use OCA\Cookbook\Controller\Implementation\CategoryImplementation;
use OCP\IRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cookbook\Controller\CategoryController
 */
class CategoryControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return CategoryController::class;
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
