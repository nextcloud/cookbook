<?php

namespace OCA\Cookbook\tests\Integration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20210701093123FailedReleaseTest extends AbstractMigrationTestCase {
	public function setUp(): void {
		// Replace the migration by old one
		$cur = getcwd();
		chdir('lib/Migration');
		unlink('Version000000Date20210427082010.php');
		exec("wget -q 'https://raw.githubusercontent.com/nextcloud/cookbook/432b0f72f9298b70affc963c2c7cd488d0c6a1fc/lib/Migration/Version000000Date20210427082010.php'");
		exec('md5sum Version000000Date20210427082010.php');
		chdir($cur);
		
		parent::setUp();
	}
	
	protected function tearDown(): void {
		// rsync -a /cookbook/ custom_apps/cookbook/ --delete --delete-delay --delete-excluded --exclude /.git --exclude /.github/actions/run-tests/volumes --exclude /docs --exclude /node_modules/
		exec('rsync -a /cookbook/lib/Migration/ lib/Migration --delete --delete-delay --delete-excluded');
		parent::tearDown();
	}
	
	/**
	 * In an intermediate version (0.9.0) a primary key was added. This can cause trouble (especially on postgresql).
	 * @see https://github.com/nextcloud/cookbook/issues/779
	 *
	 * @runInSeparateProcess
	 * @covers \OCA\Cookbook\Migration\Version000000Date20210701093123
	 */
	public function testRemovalOfPrimaryKey() {
		// Preparations done. Start the migration
		$this->migrationService->migrate('000000Date20210701093123');
		
		// Migration has not thrown an Exception
		$this->assertTrue(true);
	}
	
	protected function getPreviousMigrationName(): string {
		return '000000Date20210427082010';
	}
}
