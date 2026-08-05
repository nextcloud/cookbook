<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000000Date20190910100911 extends SimpleMigrationStep {
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	#[\Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$recipes_table = $schema->getTable('cookbook_recipes');

		if (!$recipes_table->hasColumn('user_id')) {
			$recipes_table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => 'empty',
			]);
		}

		$keywords_table = $schema->getTable('cookbook_keywords');

		if (!$keywords_table->hasColumn('user_id')) {
			$keywords_table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => 'empty',
			]);
		}

		return $schema;
	}
}
