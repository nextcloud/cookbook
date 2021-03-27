<?php

namespace OCA\Cookbook\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000000Date20200315121603 extends SimpleMigrationStep
{

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('cookbook_categories')) {
			$table = $schema->getTable('cookbook_categories');
		} else {
			$table = $schema->createTable('cookbook_categories');
		}

		if (!$table->hasColumn('recipe_id')) {
			$table->addColumn('recipe_id', 'integer', [
				'notnull' => true,
			]);
		}

		if (!$table->hasColumn('name')) {
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 256,
			]);
		}
		
		if (!$table->hasColumn('user_id')) {
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
		}
		
		return $schema;
	}
}
