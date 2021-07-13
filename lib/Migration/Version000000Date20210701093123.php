<?php

declare(strict_types=1);

namespace OCA\Cookbook\Migration;

use OCP\IDBConnection;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Closure;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version000000Date20210701093123 extends SimpleMigrationStep {

    /**
     * @var IDBConnection
     */
    private $db;
    
    public function __construct(IDBConnection $db){
        $this->db = $db;
    }
    
    function preSchemaChange(IOutput $output, \Closure $schemaClosure, array $options){
        $this->db->beginTransaction();
        try {
            
            $qb = $this->db->getQueryBuilder();
            
            // Fetch all rows that are non-unique
            $qb->selectAlias('n.user_id', 'user')
                ->selectAlias($qb->createFunction('COUNT(n.user_id)'), 'cnt')
                ->from('cookbook_names', 'n')
                ->groupBy('n.user_id', 'n.recipe_id')
                ->having('cnt > 1');
            //echo $qb->getSQL() . "\n";
            
            $cursor = $qb->execute();
            $result = $cursor->fetchAll();
            
            if(sizeof($result) > 0){
                // We have to fix the database
                throw new \Exception('This is not yet implemented.');
            }
            
            // Finish the transaction
            $this->db->commit();
        } catch (\Exception $e) {
            // Abort the transaction
            $this->db->rollBack();
            throw $e;
        }
    }
    
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
