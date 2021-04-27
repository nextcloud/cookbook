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
class Version000000Date20210427082010 extends SimpleMigrationStep {

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
		if (! $namesTable->hasPrimaryKey()) {
			$namesTable->setPrimaryKey(['recipe_id']);
		}
		
		$categoriesTable = $schema->getTable('cookbook_categories');
		if (! $categoriesTable->hasIndex('recipe_idx')) {
			$categoriesTable->addIndex([
				'user_id',
				'recipe_id',
			], 'recipe_idx');
		}
		
		$keywordsTable = $schema->getTable('cookbook_keywords');
		if (! $keywordsTable->hasIndex('recipe_idx')) {
			$keywordsTable->addIndex([
				'user_id',
				'recipe_id',
			], 'recipe_idx');
		}
		
		return $schema;
	}
}
