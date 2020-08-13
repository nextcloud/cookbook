<?php

namespace OCA\Cookbook\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use Doctrine\DBAL\Types\Type;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\IDBConnection;

class RecipeDb {

    private const DB_TABLE_RECIPES = 'cookbook_names';
    
    private $db;
    
    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     * @deprecated
     */
    public function findRecipeById(int $id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from('cookbook_recipe')
            ->where('id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

        $cursor = $qb->execute();
        $row = $cursor->fetch();
        $cursor->closeCursor();

        return $row;
    }
    
    public function deleteRecipeById(int $id) {
        $qb = $this->db->getQueryBuilder();

        $qb->delete('cookbook_names')
            ->where('recipe_id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        
        $qb->execute();
        
        $qb->delete('cookbook_keywords')
            ->where('recipe_id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        
        $qb->delete('cookbook_categories')
            ->where('recipe_id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

        $qb->execute();
    }
    
    public function findAllRecipes(string $user_id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from('cookbook_names', 'r')
            ->where('user_id = :user')
            ->orderBy('r.name');
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();

        return $this->unique($result);
    }

    public function unique(array $result) {
        // NOTE: This post processing shouldn't be necessary
        // When sharing recipes with other users, they are occasionally returned twice
        // See issue #149 for details
        $unique_result = [];

        foreach($result as $recipe) {
            if(!isset($recipe['recipe_id'])) { continue; }
            if(isset($unique_result[$recipe['recipe_id']])) { continue; }

            $unique_result[$recipe['recipe_id']] = $recipe;
        }
        
        return array_values($unique_result);
    }

    public function findAllKeywords(string $user_id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('k.name')
			->selectAlias($qb->createFunction('COUNT(k.recipe_id)'), 'recipe_count')
            ->from('cookbook_keywords', 'k')
            ->where('user_id = :user AND k.name != \'\'')
            ->groupBy('k.name')
            ->orderBy('k.name');
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();
		
        $result = array_unique($result, SORT_REGULAR);
        $result = array_filter($result);
		
        return $result;
    }
    
    public function findAllCategories(string $user_id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('k.name')
			->selectAlias($qb->createFunction('COUNT(k.recipe_id)'), 'recipe_count')
            ->from('cookbook_categories', 'k')
            ->where('user_id = :user AND k.name != \'\'')
            ->groupBy('k.name')
            ->orderBy('k.name');
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();
		
        $result = array_unique($result, SORT_REGULAR);
        $result = array_filter($result);
		
        return $result;
    }
    
    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     */
    public function getRecipesByCategory(string $category, string $user_id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select(['r.recipe_id', 'r.name'])
            ->from('cookbook_categories', 'k')
            ->where('k.name = :category')
            ->andWhere('k.user_id = :user')
            ->setParameter('category', $category, TYPE::STRING)
            ->setParameter('user', $user_id, TYPE::STRING);
        
        $qb->join('k', 'cookbook_names', 'r', 'k.recipe_id = r.recipe_id');

        $qb->groupBy(['r.name', 'r.recipe_id']);
        $qb->orderBy('r.name');

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();

        return $this->unique($result);
    }
    
    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     */
    public function findRecipes(array $keywords, string $user_id) {
        $has_keywords = $keywords && is_array($keywords) && sizeof($keywords) > 0 && $keywords[0];

        if(!$has_keywords) { return $this->findAllRecipes($user_id); }

        $qb = $this->db->getQueryBuilder();

        $qb->select(['r.recipe_id', 'r.name'])
            ->from('cookbook_keywords', 'k');
        
        $paramIdx = 1;
        $params = [];
        $types = [];
        
        foreach ($keywords as $keyword)
        {
            $lowerKeyword = strtolower($keyword);
            
            $qb->orWhere("LOWER(k.name) LIKE :keyword$paramIdx");
            $qb->orWhere("LOWER(r.name) LIKE :keyword$paramIdx");
            
            $params["keyword$paramIdx"] = "%$lowerKeyword%";
            $types["keyword$paramIdx"] = Type::STRING;
            $paramIdx++;
            
        }

        $qb->andWhere('k.user_id = :user');

        $qb->setParameters($params, $types);
        $qb->setParameter('user', $user_id, TYPE::STRING);
        
        $qb->join('k', 'cookbook_names', 'r', 'k.recipe_id = r.recipe_id');

        $qb->groupBy(['r.name', 'r.recipe_id']);
        $qb->orderBy('r.name');

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();

        return $this->unique($result);
    }
    
    public function emptySearchIndex(string $user_id) {
        $qb = $this->db->getQueryBuilder();
        
        $qb->delete('cookbook_names')
            ->where('user_id = :user')
            ->orWhere('user_id = :empty');
        $qb->setParameter('user', $user_id, TYPE::STRING);
        $qb->setParameter('empty', 'empty', TYPE::STRING);
        
        $qb->execute();
        
        $qb->delete('cookbook_keywords')
            ->where('user_id = :user')
            ->orWhere('user_id = :empty');
        $qb->setParameter('user', $user_id, TYPE::STRING);
        $qb->setParameter('empty', 'empty', TYPE::STRING);
        
        $qb->delete('cookbook_categories')
            ->where('user_id = :user')
            ->orWhere('user_id = :empty');
        $qb->setParameter('user', $user_id, TYPE::STRING);
        $qb->setParameter('empty', 'empty', TYPE::STRING);
        
        $qb->execute();
    }

    private function isRecipeEmpty($json) {}

    public function indexRecipeFile(File $file, string $user_id) {
        $json = json_decode($file->getContent(), true);

        if(!$json || !isset($json['name']) || $json['name'] === 'No name') { return; }

        $id = (int) $file->getParent()->getId();
        $json['id'] = $id;
        $qb = $this->db->getQueryBuilder();

        // Insert recipe
        $qb->delete('cookbook_names')
            ->where('recipe_id = :id')
            ->andWhere('user_id = :user');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $qb->execute();

        $qb->insert('cookbook_names')
            ->values([
                'recipe_id' => ':id',
                'name' => ':name',
                'user_id' => ':user',
            ]);
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        $qb->setParameter('name', $json['name'], Type::STRING);
        $qb->setParameter('user', $user_id, Type::STRING);
        
        $qb->execute();

        // Insert keywords
        $qb->delete('cookbook_keywords')
            ->where('recipe_id = :id')
            ->andWhere('user_id = :user');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        $qb->setParameter('user', $user_id, TYPE::STRING);
        
        $qb->execute();
        
        if(isset($json['keywords']) && !empty($json['keywords'])) {
            foreach(explode(',', $json['keywords']) as $keyword) {
                $keyword = trim($keyword);

                $qb->insert('cookbook_keywords')
                    ->values([
                        'recipe_id' => ':id',
                        'name' => ':keyword',
                        'user_id' => ':user',
                    ]);
                $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
                $qb->setParameter('keyword', $keyword, Type::STRING);
                $qb->setParameter('user', $user_id, Type::STRING);

                try {         
                    $qb->execute();

                } catch(\Exception $e) {
                    // Keyword didn't meet restrictions, skip it

                }
            }
        }
        
        // Insert category
        // NOTE: We're using * as a placeholder for no category
        $qb->delete('cookbook_categories')
            ->where('recipe_id = :id')
            ->andWhere('user_id = :user');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        $qb->setParameter('user', $user_id, TYPE::STRING);
                
        $qb->execute();
        
        if(!isset($json['recipeCategory']) || empty($json['recipeCategory'])) {
            $json['recipeCategory'] = '*';
        } else if(is_array($json['recipeCategory'])) {
            $json['recipeCategory'] = reset($json['recipeCategory']);
        }
        
        $qb->insert('cookbook_categories')
            ->values([
                'recipe_id' => ':id',
                'name' => ':category',
                'user_id' => ':user',
            ]);

        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        $qb->setParameter('category', $json['recipeCategory'], Type::STRING);
        $qb->setParameter('user', $user_id, Type::STRING);

        try {
            $qb->execute();

        } catch(\Exception $e) {
            // Category didn't meet restrictions, skip it
        
        }
    }
    
    /**
     * @param array $ids
     */
    public function deleteRecipes(array $ids)
    {
        if(!is_array($ids) || empty($ids))
            return;
        
        $qb = $this->db->getQueryBuilder();
        
        $qb->delete(self::DB_TABLE_RECIPES);
        
        foreach ($ids as $id)
            $qb->orWhere("recipe_id = $id");
        
        $qb->execute();
    }
    
    /**
     * @param array $recipes
     */
    public function insertRecipes(array $recipes, string $userId)
    {
        if(!is_array($recipes) || empty($recipes))
            return;
        
        $qb = $this->db->getQueryBuilder();
        
        $qb->insert(self::DB_TABLE_RECIPES)
            ->values(array(
                'recipe_id' => ':id',
                'user_id' => ':userid',
                'name' => ':name'
            ));
        
        $qb->setParameter('userid', $userId);
        
        foreach ($recipes as $recipe)
        {
            $qb->setParameter('id', $recipe['id'], Type::INTEGER);
            $qb->setParameter('name', $recipe['name'], Type::STRING);
            
            $qb->execute();
        }
    }
    
    public function updateRecipes(array $recipes)
    {
        if(!is_array($recipes) || empty($recipes))
            return;
        
        $qb = $this->db->getQueryBuilder();
        
        foreach ($recipes as $recipe)
        {
            $qb->update(self::DB_TABLE_RECIPES)
                ->where('recipe_id = :id');
            
            $literal = array();
            $literal['name'] = $qb->expr()->literal($recipe['name'], IQueryBuilder::PARAM_STR);
            $qb->set('name', $literal['name']);
            
            $qb->setParameter('id', $recipe['id']);
            
            $qb->execute();
        }
    }
}

?>
