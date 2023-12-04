<?php

namespace OCA\Cookbook\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000000Date20190910223344 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Because of Postgres, we have to drop this table entirely in order to remove the primary key and allow multi user tables
		$schema->dropTable('cookbook_recipes');

		// Also because of Postgres, we have to start using another name, because we can't recreate the table with the same name in the same migration method
		$table = $schema->createTable('cookbook_names');
		$table->addColumn('recipe_id', 'integer', [
			'notnull' => true,
		]);
		$table->addColumn('name', 'string', [
			'notnull' => true,
			'length' => 128,
		]);
		$table->addColumn('user_id', 'string', [
			'notnull' => true,
			'length' => 64,
		]);

		return $schema;
	}
}
