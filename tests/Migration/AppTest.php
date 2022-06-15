<?php

namespace OCA\Cookbook\tests\Migration;

use OCP\App\IAppManager;
use OCP\AppFramework\App;
use PHPUnit\Framework\TestCase;

/**
 * This test shows how to make a small Integration Test. Query your class
 * directly from the container, only pass in mocks if needed and run your tests
 * against the database
 */
class AppTest extends TestCase {
	private $container;

	public function setUp(): void {
		resetEnvironmentToBackup();
		
		parent::setUp();
		$app = new App('cookbook');
		$this->container = $app->getContainer();
	}

	public function testAppInstalled() {
		$appManager = $this->container->query(IAppManager::class);
		$this->assertTrue($appManager->isInstalled('cookbook'));
	}
}
