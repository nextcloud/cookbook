<?php

declare(strict_types=1);

namespace OCA\Cookbook\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version000000Date20210701093123 extends SimpleMigrationStep {

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
