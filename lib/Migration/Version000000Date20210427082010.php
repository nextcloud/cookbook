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
		
		$categoriesTable = $schema->getTable('cookbook_categories');
		if (! $categoriesTable->hasIndex('categories_recipe_idx')) {
			$categoriesTable->addIndex([
				'user_id',
				'recipe_id',
			], 'categories_recipe_idx');
		}
		
		$keywordsTable = $schema->getTable('cookbook_keywords');
		if (! $keywordsTable->hasIndex('keywords_recipe_idx')) {
			$keywordsTable->addIndex([
				'user_id',
				'recipe_id',
			], 'keywords_recipe_idx');
		}
		
		return $schema;
	}
}
