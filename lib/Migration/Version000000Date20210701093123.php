<?php

declare(strict_types=1);

namespace OCA\Cookbook\Migration;

use OCP\IDBConnection;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Closure;
use OCP\DB\QueryBuilder\IQueryBuilder;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version000000Date20210701093123 extends SimpleMigrationStep {
	/**
	 * @var IDBConnection
	 */
	private $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	public function preSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
		$this->db->beginTransaction();
		try {
			$qb = $this->db->getQueryBuilder();

			// Fetch all rows that are non-unique
			$qb->selectAlias('n.user_id', 'user')
				->selectAlias('n.recipe_id', 'recipe')
				->from('cookbook_names', 'n')
				->groupBy('n.user_id', 'n.recipe_id')
				->having('COUNT(*) > 1');
			//echo $qb->getSQL() . "\n";

			$cursor = $qb->execute();
			$result = $cursor->fetchAll();

			if (sizeof($result) > 0) {
				// We have to fix the database

				// Drop all redundant rows
				$qb = $this->db->getQueryBuilder();
				$qb->delete('cookbook_names')
					->where(
						'user_id = :user',
						'recipe_id = :recipe'
					);

				$qb2 = $this->db->getQueryBuilder();
				$qb2->update('preferences')
					->set('configvalue', $qb->expr()->literal('1', IQueryBuilder::PARAM_STR))
					->where(
						'userid = :user',
						'appid = :app',
						'configkey = :property'
					);
				$qb2->setParameter('app', 'cookbook');
				$qb2->setParameter('property', 'last_index_update');

				foreach ($result as $r) {
					$qb->setParameter('user', $r['user']);
					$qb->setParameter('recipe', $r['recipe']);
					$qb->execute();

					$qb2->setParameter('user', $r['user']);
					$qb2->execute();
				}
			}

			// Finish the transaction
			$this->db->commit();
		} catch (\Exception $e) {
			// Abort the transaction
			$this->db->rollBack();
			throw $e;
		}
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/**
		 * @var ISchemaWrapper $schema
		 */
		$schema = $schemaClosure();

		$namesTable = $schema->getTable('cookbook_names');
		if ($namesTable->hasPrimaryKey()) {
			$namesTable->dropPrimaryKey();
		}
		if (! $namesTable->hasIndex('names_recipe_idx')) {
			$namesTable->addUniqueIndex([
				'recipe_id',
				'user_id'
			], 'names_recipe_idx');
		}

		return $schema;
	}
}
