<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\AppInfo;

use OCA\Cookbook\AppInfo\Application;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase {
	public function testRegistersExistingSensitiveMethods(): void {
		$registered = [];
		$context = $this->createMock(IRegistrationContext::class);
		$context->method('registerSensitiveMethods')->willReturnCallback(function (string $class, array $methods) use (&$registered) {
			$registered[$class] = $methods;
		});

		(new Application())->register($context);

		$this->assertNotEmpty($registered);
		foreach ($registered as $class => $methods) {
			foreach ($methods as $method) {
				$this->assertTrue(method_exists($class, $method), "$class::$method is registered as sensitive but does not exist");
			}
		}
	}
}
