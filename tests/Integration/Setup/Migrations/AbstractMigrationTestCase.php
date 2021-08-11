<?php

namespace OCA\Cookbook\tests\Integration\Setup\Migrations;

use OCP\IDBConnection;
use OCP\Util;
use OC\DB\Connection;
use OC\DB\SchemaWrapper;
use PHPUnit\Framework\TestCase;
use OCP\AppFramework\App;
use OC\DB\MigrationService;
use OCP\AppFramework\IAppContainer;

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
    
    protected abstract function getPreviousMigrationName(): ?string;
    
    private const TMP_MIGRATIONS = '/tmp/old-migrations';
    
    public function setUp(): void {
        resetEnvironmentToBackup('plain');
        
        parent::setUp();
        
        $this->hideMigrations();
        $this->enableApp();
        
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
        $this->migrationService = new MigrationService('cookbook', $this->connection);
        $this->assertIsObject($this->migrationService);
        
        // Reinstall app partially (just before the migration)
        $this->restoreMigrations();
        $migrationBefore = $this->getPreviousMigrationName();
        if(! empty($migrationBefore)) {
            runOCCCommand(['migrations:migrate', 'cookbook', $migrationBefore]);
        }
    }
    
    protected function tearDown(): void {
        unset($this->container);
        unset($this->db);
        unset($this->migrationService);
        unset($this->connection);
    }
    
    private function enableApp() {
        runOCCCommand(['app:enable', 'cookbook']);
    }
    
    private function hideMigrations() {
        if(! file_exists(self::TMP_MIGRATIONS)) {
            mkdir(self::TMP_MIGRATIONS);
        }
        
        exec('mv lib/Migration/* ' . self::TMP_MIGRATIONS);
    }
    
    private function restoreMigrations() {
        exec('mv ' . self::TMP_MIGRATIONS . '/* lib/Migration');
    }
    
}
