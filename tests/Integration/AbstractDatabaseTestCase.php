<?php

namespace OCA\Cookbook\tests\Integration;

use ChristophWurst\Nextcloud\Testing\DatabaseTransaction;
use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Cookbook\AppInfo\Application;
use Psr\Container\ContainerInterface;

abstract class AbstractDatabaseTestCase extends TestCase {
	use DatabaseTransaction;

	/** @var ContainerInterface */
	protected $ncContainer;

	protected function setUp(): void {
		parent::setUp();
		$this->startTransaction();

		$app = new Application();
		$this->ncContainer = $app->getContainer();
	}

	protected function query(string $name) {
		return $this->ncContainer->get($name);
	}

	protected function tearDown(): void {
		$this->rollbackTransation();
		parent::tearDown();

		$this->resetDataFolder();
	}

	private function resetDataFolder() {
		$cmd = "rsync -a --delete --delete-delay /dumps/current/plain/data/ /var/www/html/data";
		$output = [];
		$ret = 0;
		exec($cmd, $output, $ret);

		if ($ret !== 0) {
			// The rsync did fail
			echo "\nThe recovery of the data failed. Standard output:\n";
			print_r($output);
			echo "The return code was $ret.\n";
		}
	}
}
