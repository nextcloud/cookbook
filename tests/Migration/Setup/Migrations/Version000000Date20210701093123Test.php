<?php

namespace OCA\Cookbook\tests\Migration\Setup\Migrations;

use OCP\DB\QueryBuilder\IQueryBuilder;

include_once __DIR__ . '/AbstractMigrationTestCase.php';

class Version000000Date20210701093123Test extends AbstractMigrationTestCase {
	/**
	 * @dataProvider dataProvider
	 * @runInSeparateProcess
	 * @covers \OCA\Cookbook\Migration\Version000000Date20210701093123
	 * @param mixed $data
	 * @param mixed $updatedUsers
	 */
	public function testRedundantEntriesInDB($data, $updatedUsers) {
		// Add recipe dummy data from data provider
		$qb = $this->db->getQueryBuilder();
		$qb->insert('cookbook_names')
			->values([
				'recipe_id' => ':recipe',
				'user_id' => ':user',
				'name' => ':name',
			]);
		$qb->setParameter('name', 'name of the recipe');
		foreach ($data as $d) {
			$qb->setParameter('user', $d[0]);
			$qb->setParameter('recipe', $d[1]);

			$this->assertEquals(1, $qb->execute());
		}

		// Initialize configuration values to track reindex timestamps
		$current = time();

		$qb = $this->db->getQueryBuilder();
		$qb->insert('preferences')
			->values([
				'userid' => ':user',
				'appid' => ':appid',
				'configkey' => ':property',
				'configvalue' => ':value',
			]);

		$qb->setParameter('value', $current, IQueryBuilder::PARAM_STR);
		$qb->setParameter('appid', 'cookbook');
		$qb->setParameter('property', 'last_index_update');

		$users = array_unique(array_map(function ($x) {
			return $x[0];
		}, $data));
		foreach ($users as $u) {
			$qb->setParameter('user', $u);
			$this->assertEquals(1, $qb->execute());
		}

		// Run the migration under test
		$this->migrationService->migrate('000000Date20210701093123');

		// Get the (updated) reindex timestamps
		$qb = $this->db->getQueryBuilder();
		$qb->select('userid', 'configvalue')
			->from('preferences')
			->where(
				'appid = :appid',
				'configkey = :property'
				);
		$qb->setParameter('appid', 'cookbook');
		$qb->setParameter('property', 'last_index_update');

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();

		// Filter those entries from the configuration that were marked as to be reindexed
		$result = array_filter($result, function ($x) use ($current) {
			return $x['configvalue'] < $current;
		});
		// Map the array to contain only the corresponding user names
		$changedUsers = array_map(function ($x) {
			return $x['userid'];
		}, $result);

		// Sort the arrays to allow comparision of them
		sort($changedUsers);
		sort($updatedUsers);

		$this->assertEquals($updatedUsers, $changedUsers);
	}

	public function dataProvider() {
		return [
			'caseA' => [
				[
					['alice', 123],
					['alice', 124],
					['bob', 125]
				],
				[],
			],
			'caseB' => [
				[
					['alice', 123],
					['alice', 124],
					['bob', 124],
					['bob', 125],
				],
				[],
			],
			'caseC' => [
				[
					['alice', 123],
					['alice', 124],
					['bob', 124],
					['bob', 124],
					['bob', 125],
				],
				['bob']
			],
			'caseD' => [
				[
					['alice', 124],
					['alice', 124],
					['bob', 124],
					['bob', 124],
					['bob', 125],
				],
				['alice', 'bob']
			],
		];
	}

	protected function getPreviousMigrationName(): string {
		return '000000Date20210427082010';
	}
}
