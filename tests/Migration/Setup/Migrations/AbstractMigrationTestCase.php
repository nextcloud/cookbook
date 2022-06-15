<?php

namespace OCA\Cookbook\tests\Migration\Setup\Migrations;

use Doctrine\DBAL\Schema\Table;
use OCP\IDBConnection;
use OCP\Util;
use OC\DB\Connection;
use OC\DB\SchemaWrapper;
use PHPUnit\Framework\TestCase;
use OCP\AppFramework\App;
use OC\DB\MigrationService;
use OCP\AppFramework\IAppContainer;

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
	
	public function setUp(): void {
		parent::setUp();
		
		resetEnvironmentToBackup('plain');
		
		$this->hideMigrations();
		$this->enableApp();
		$this->restoreMigrations();
		
		$app = new App('cookbook');
		$this->container = $app->getContainer();
		
		/**
		 * @var IDBConnection $db
		 */
		$this->db = $this->container->query(IDBConnection::class);
		$this->assertIsObject($this->db);
		/**
		 * @var SchemaWrapper $schema
		 */
		$this->schema = $this->container->query(SchemaWrapper::class);
		$this->assertIsObject($this->schema);
		
		if (Util::getVersion()[0] >= 21) {
			$this->connection = \OC::$server->query(Connection::class);
		} else {
			$this->connection = $this->db;
		}
		
		if ($_ENV['INPUT_DB'] === 'sqlite') {
			$this->resetSQLite();
		}
		
		$this->migrationService = new MigrationService('cookbook', $this->connection);
		$this->assertIsObject($this->migrationService);
		
		// Reinstall app partially (just before the migration)
		$migrationBefore = $this->getPreviousMigrationName();
		if (! empty($migrationBefore)) {
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
		if (! file_exists(self::TMP_MIGRATIONS)) {
			mkdir(self::TMP_MIGRATIONS);
		}
		
		exec('mv lib/Migration/* ' . self::TMP_MIGRATIONS);
	}
	
	private function restoreMigrations() {
		exec('mv ' . self::TMP_MIGRATIONS . '/* lib/Migration');
	}
	
	private function resetSQLite(): void {
		$allTables = $this->schema->getTables();
		$tables = array_filter($allTables, function (Table $t) {
			return str_starts_with($t->getName(), 'oc_cookbook');
		});
		
		/**
		 * @var Table $t
		 */
		foreach ($tables as $t) {
			$this->schema->dropTable(preg_replace('/^oc_/', '', $t->getName()));
		}
		
		$qb = $this->db->getQueryBuilder();
		$qb->delete('migrations')->where('app = :app');
		$qb->setParameter('app', 'cookbook');
		$qb->execute();
		
		$this->schema->performDropTableCalls();
		
		$this->renewSchema();
	}
}
