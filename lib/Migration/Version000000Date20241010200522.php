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
class Version000000Date20241010200522 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper */
		$schema = $schemaClosure();

		$table = $schema->getTable('cookbook_categories');

		if (!$table->hasColumn('id')) {
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
			]);
			$table->setPrimaryKey(['id']);
		}

		$table = $schema->getTable('cookbook_names');

		if (!$table->hasColumn('id')) {
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
			]);
			$table->setPrimaryKey(['id']);
		}

		$table = $schema->getTable('cookbook_keywords');

		if (!$table->hasColumn('id')) {
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
			]);
			$table->setPrimaryKey(['id']);
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
