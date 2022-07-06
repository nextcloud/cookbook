<?php

namespace OCA\Cookbook\tests\Migration\Setup\Migrations;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20220703174647Test extends AbstractMigrationTestCase {
	/**
	 * dataProvider dataProvider
	 * @runInSeparateProcess
	 * @covers \OCA\Cookbook\Migration\Version000000Date20220703174647
	 * @param mixed $data
	 * @param mixed $updatedUsers
	 */
	public function testRedundantEntriesInDB(/*$data, $updatedUsers*/) {
		// Add recipe dummy data from data provider
		$qb = $this->db->getQueryBuilder();
		$qb->insert('cookbook_names')
			->values([
				'recipe_id' => ':recipe',
				'user_id' => ':user',
				'name' => ':name',
			]);
		$qb->setParameter('name', 'name of the recipe');
		$qb->setParameter('user', 'username');
		$qb->setParameter('recipe', 1234);

		$this->assertEquals(1, $qb->execute());

		$table = $this->schema->getTable('cookbook_names');
		$this->assertFalse($table->hasColumn('dateCreated'));
		$this->assertFalse($table->hasColumn('dateModified'));

		// Run the migration under test
		$this->migrationService->migrate('000000Date20220703174647');
		$this->renewSchema();

		$table = $this->schema->getTable('cookbook_names');
		$this->assertTrue($table->hasColumn('dateCreated'));
		$this->assertTrue($table->hasColumn('dateModified'));

		$qb = $this->db->getQueryBuilder();
		$qb->select('dateCreated', 'dateModified')->from('cookbook_names');
		$res = $qb->execute();
		$data = $res->fetchAll();

		$this->assertEquals(1, count($data));
		$row = $data[0];
		$this->assertEquals(2, count($row));
		$this->assertNull($row['dateCreated']);
		$this->assertNull($row['dateModified']);


		//$this->assertEquals([null, null], $data[0]);
	}


	protected function getPreviousMigrationName(): string {
		return '000000Date20210701093123';
	}
}
