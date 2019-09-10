<?php

namespace OCA\Cookbook\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;
use Doctrine\DBAL\Types\Type;

class Version000000Date20190910100911 extends SimpleMigrationStep {
    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        $recipes_table = $schema->getTable('cookbook_recipes');
        
        if(!$recipes_table->hasColumn('user_id')) {
            $recipes_table->addColumn('user_id', Type::STRING, [
                'notnull' => true,
                'length' => 64,
                'default' => 'empty',
            ]);
        }
        
        $keywords_table = $schema->getTable('cookbook_keywords');
        
        if(!$keywords_table->hasColumn('user_id')) {
            $keywords_table->addColumn('user_id', Type::STRING, [
                'notnull' => true,
                'length' => 64,
                'default' => 'empty',
            ]);
        }

        return $schema;
    }
}

?>
