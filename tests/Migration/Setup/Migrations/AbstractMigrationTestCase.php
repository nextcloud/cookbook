<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Migration\Setup\Migrations;

use OC\DB\Connection;
use OC\DB\MigrationService;
use OC\DB\SchemaWrapper;
use OCP\AppFramework\App;
use OCP\IAppContainer;
use OCP\IDBConnection;
use OCP\Util;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
abstract class AbstractMigrationTestCase extends TestCase {
	/**
	 * @var IAppContainer
	 */
	protected $container;

	/**
	 * @var IDBConnection
	 */
	protected $db;

	/**
	 * @var MigrationService
	 */
	protected $migrationService;

	/**
	 * @var SchemaWrapper
	 */
	protected $schema;

	protected $connection;

	abstract protected function getPreviousMigrationName(): ?string;

	private const TMP_MIGRATIONS = '/tmp/old-migrations';
	private const DB_TABLE_PREFIX = 'oc_';

	public function setUp(): void {
		parent::setUp();

		$this->hideMigrations();
		$this->enableApp();
		$this->restoreMigrations();

		$app = new App('cookbook');
		$this->container = $app->getContainer();

		/**
		 * @var IDBConnection $db
		 */
		$this->db = $this->container->get(IDBConnection::class);
		$this->assertIsObject($this->db);
		/**
		 * @var SchemaWrapper $schema
		 */
		$this->schema = $this->container->get(SchemaWrapper::class);
		$this->assertIsObject($this->schema);

		if (Util::getVersion()[0] >= 21) {
			$this->connection = \OC::$server->get(Connection::class);
		} else {
			$this->connection = $this->db;
		}

		$this->resetCookbookTables();

		$this->migrationService = new MigrationService('cookbook', $this->connection);
		$this->assertIsObject($this->migrationService);

		// Reinstall app partially (just before the migration)
		$migrationBefore = $this->getPreviousMigrationName();
		if (!empty($migrationBefore)) {
			// We need to run a migration beforehand
			$this->migrationService->migrate($migrationBefore);
			$this->renewSchema();
		}
	}

	protected function tearDown(): void {
		unset($this->container);
		unset($this->db);
		unset($this->migrationService);
		unset($this->connection);
	}

	protected function renewSchema(): void {
		$this->schema = new SchemaWrapper($this->connection);
	}

	private function enableApp() {
		runOCCCommand(['app:enable', 'cookbook']);
	}

	private function hideMigrations() {
		if (!file_exists(self::TMP_MIGRATIONS)) {
			mkdir(self::TMP_MIGRATIONS);
		}

		exec('mv lib/Migration/* ' . self::TMP_MIGRATIONS);
	}

	private function restoreMigrations() {
		exec('mv ' . self::TMP_MIGRATIONS . '/* lib/Migration');
	}

	private function resetCookbookTables(): void {
		// The container-built SchemaWrapper wraps an empty Schema, so it sees no tables
		$this->renewSchema();
		$this->dropCookbookTables();
		$this->deleteCookbookRows('migrations', 'app');
		$this->deleteCookbookRows('preferences', 'appid');
		$this->renewSchema();
	}

	private function dropCookbookTables(): void {
		foreach ($this->schema->getTables() as $table) {
			$name = $table->getName();
			if (str_starts_with($name, self::DB_TABLE_PREFIX . 'cookbook')) {
				$this->schema->dropTable(substr($name, strlen(self::DB_TABLE_PREFIX)));
			}
		}
		$this->schema->performDropTableCalls();
	}

	private function deleteCookbookRows(string $table, string $appColumn): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($table)->where($qb->expr()->eq($appColumn, $qb->createNamedParameter('cookbook')));
		$qb->executeStatement();
	}
}
