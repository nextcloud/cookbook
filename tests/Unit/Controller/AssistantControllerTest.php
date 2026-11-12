<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Controller;

require_once(__DIR__ . '/AbstractControllerTestCase.php');

use OCA\Cookbook\Controller\AssistantController;
use OCA\Cookbook\Controller\Implementation\AssistantImplementation;

/**
 * @covers OCA\Cookbook\Controller\AssistantController
 */
class AssistantControllerTest extends AbstractControllerTestCase {
	protected function getClassName(): string {
		return AssistantController::class;
	}

	protected function getImplementationClassName(): string {
		return AssistantImplementation::class;
	}

	protected function getMethodsAndParameters(): array {
		return [
			['name' => 'create', 'once' => true],
			['name' => 'show', 'args' => [[123]]],
			['name' => 'cancel', 'args' => [[123]], 'once' => true],
		];
	}
}
