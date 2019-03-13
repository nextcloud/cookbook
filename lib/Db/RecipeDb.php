<?php

namespace OCA\Cookbook\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class RecipeDb {
    private $db;

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     */
    public function findRecipeById(int $id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from('cookbook_recipes')
            ->where(
                $qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
            );

        $cursor = $qb->execute();
        $row = $cursor->fetch();
        $cursor->closeCursor();

        return $row;
    }
    
    public function findAllRecipes() {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from('cookbook_recipes');

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();

        return $result;
    }

    public function findAllKeywords() {
        $qb = $this->db->getQueryBuilder();

        $qb->select('k.name')
            ->from('cookbook_keywords', 'k')
            ->groupBy('k.name');

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();

        return $result;
    }
    
    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     */
    public function findRecipes($keywords) {
        $result = [];

        $has_keywords = $keywords && is_array($keywords) && sizeof($keywords) > 0 && $keywords[0];

        if(!$has_keywords) { return $this->findAllRecipes(); }

        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from('cookbook_keywords', 'k')
            ->where('k.name = \'' . $keywords[0] . '\'');

        for($i = 1; $i < sizeof($keywords); $i++) {
            $qb->orWhere('k.name = \'' . $keywords[$i] . '\'');
        }
        
        $qb->join('k', 'cookbook_recipes', 'rk', 'k.recipe_id = rk.recipe_id'); 

        $cursor = $qb->execute();
        $result = array_merge($result, $cursor->fetchAll());
        $cursor->closeCursor();

        return $result;
    }
    
    public function emptySearchIndex() {
        $qb = $this->db->getQueryBuilder();
        
        $qb->delete('cookbook_recipes');
        
        $qb->execute();
        
        $qb->delete('cookbook_keywords');
        
        $qb->execute();
    }

    public function indexRecipeFile($file) {
        $json = json_decode($file->getContent(), true);

        if(!$json || !isset($json['name'])) { return; }
        
        $id = $file->getId();
        $json['id'] = $id;
        $qb = $this->db->getQueryBuilder();

        // Insert recipe 
        $qb->delete('cookbook_recipes')
            ->where(
               $qb->expr()->eq('recipe_id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
            );
        
        $qb->execute();

        $qb->insert('cookbook_recipes')
            ->values([
                'recipe_id' => $id,
                'name' => '\'' . $json['name'] . '\'',
            ]);
        
        $qb->execute();

        // Insert keywords 
        $qb->delete('cookbook_keywords')
            ->where(
               $qb->expr()->eq('recipe_id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
            );
        
        $qb->execute();
        
        if(isset($json['keywords'])) {
            foreach(explode(',', $json['keywords']) as $keyword) {
                $keyword = trim($keyword);   
                
                $qb->insert('cookbook_keywords')
                    ->values([
                        'recipe_id' => $id,
                        'name' => '\'' . $keyword . '\'',
                    ]);
                
                $qb->execute();
            }
        }
    }
}

?>
