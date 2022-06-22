<?php

namespace OCA\Cookbook\tests\Migration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20190312140601Test extends AbstractMigrationTestCase {
	/**
	 * @runInSeparateProcess
	 * @covers \OCA\Cookbook\Migration\Version000000Date20190312140601
	 */
	public function testCreatedTables() {

		// Run the migration under test
		$this->migrationService->migrate('000000Date20190312140601');
		$this->renewSchema();

		$this->postTestAsserts('cookbook_recipes');
		$this->postTestAsserts('cookbook_keywords');
	}

	private function postTestAsserts(string $tableName): void {
		$this->assertTrue($this->schema->hasTable($tableName));

		$table = $this->schema->getTable($tableName);
		$this->assertTrue($table->hasColumn('recipe_id'));
		$this->assertTrue($table->hasColumn('name'));

		$this->assertTrue($table->getColumn('recipe_id')->getNotnull());
		$this->assertTrue($table->getColumn('name')->getNotnull());
	}

	protected function getPreviousMigrationName(): ?string {
		return null;
	}
}
