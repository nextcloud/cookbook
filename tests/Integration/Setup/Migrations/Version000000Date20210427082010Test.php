<?php

namespace OCA\Cookbook\tests\Integration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20210427082010Test extends AbstractMigrationTestCase {
	
	/**
	 * @runInSeparateProcess
	 * @covers \OCA\Cookbook\Migration\Version000000Date20210427082010
	 */
	public function testRedundantEntriesInDB($data, $updatedUsers) {
		$categoriesTable = $this->schema->getTable('cookbook_categories');
		$this->assertFalse($categoriesTable->hasIndex('categories_recipe_idx'));
		$this->assertEquals([], $categoriesTable->getIndexes());
		
		$keywordsTable = $this->schema->getTable('cookbook_keywords');
		$this->assertFalse($keywordsTable->hasIndex('keywords_recipe_idx'));
		$this->assertEquals([], $keywordsTable->getIndexes());
		
		// Run the migration under test
		$this->migrationService->migrate('000000Date20210427082010');
		
		$this->assertTrue($categoriesTable->hasIndex('categories_recipe_idx'));
		$this->assertTrue($keywordsTable->hasIndex('keywords_recipe_idx'));
	}
	
	protected function getPreviousMigrationName(): string {
		return '000000Date20200315121603';
	}
}
