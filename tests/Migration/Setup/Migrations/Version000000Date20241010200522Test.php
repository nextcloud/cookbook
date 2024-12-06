<?php

namespace OCA\Cookbook\tests\Migration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20241010200522Test extends AbstractMigrationTestCase {
	/**
	 * @runInSeparateProcess
	 * @covers \OCA\Cookbook\Migration\Version000000Date20190312140601
	 */
	public function testCreatedTables() {

		// Run the migration under test
		$this->migrationService->migrate('000000Date20241010200522');
		$this->renewSchema();

		$this->postTestAsserts('cookbook_categories');
		$this->postTestAsserts('cookbook_names');
		$this->postTestAsserts('cookbook_keywords');
	}

	private function postTestAsserts(string $tableName): void {
		$this->assertTrue($this->schema->hasTable($tableName));

		$table = $this->schema->getTable($tableName);
		$this->assertTrue($table->hasColumn('id'));
		$this->assertTrue($table->getPrimaryKey() !== null);
	}

	protected function getPreviousMigrationName(): ?string {
		return null;
	}
}
