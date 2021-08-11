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
    
    protected $connection;
    
    protected abstract function getPreviousMigrationName(): ?string;
    
    public function setUp(): void {
        resetEnvironmentToBackup('plain');
        
        parent::setUp();
        
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
        $schema = $this->container->query(SchemaWrapper::class);
        $this->assertIsObject($schema);
        
        if (Util::getVersion()[0] >= 21) {
            $this->connection = \OC::$server->query(Connection::class);
        } else {
            $this->connection = $this->db;
        }
        $this->migrationService = new MigrationService('cookbook', $this->connection);
        $this->assertIsObject($this->migrationService);
        
        // Reinstall app partially (just before the migration)
        $migrationBefore = 'Version'.$this->getPreviousMigrationName();
        if(! empty($migrationBefore)) {
            runOCCCommand(['migrations:migrate', 'cookbook', $migrationBefore], true);
        }
    }
    
    protected function tearDown(): void {
        unset($this->container);
        unset($this->db);
        unset($this->migrationService);
        unset($this->connection);
    }
    
}
