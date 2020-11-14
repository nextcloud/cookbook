<?php

declare(strict_types=1);

namespace OCA\Cookbook\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Create a table to store the aggregate ratings in the database
 */
class Version000000Date20201114165448 extends SimpleMigrationStep {

    private const RATINGS = 'cookbook_ratings';
    
	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options)
	{
	    /**
	     * @var ISchemaWrapper $schema
	     */
	    $schema = $schemaClosure;
	    
        $tableRatings = $schema->createTable(self::RATINGS);
        
        // Set up columns
        $tableRatings->addColumn('recipe_id', 'integer', [
            'notnull' => true
        ]);
        $tableRatings->addColumn('user_id', 'string', [
            'notnull' => true,
            'length' => 64
        ]);
        $tableRatings->addColumn('rating', 'float', [
            'notnull' => true
        ]);
        
        // Set up indices
        $tableRatings->addIndex(['recipe_id', 'user_id']);
        // XXX ForeignKeyConstraints
	    
		return $schema;
	}

}
