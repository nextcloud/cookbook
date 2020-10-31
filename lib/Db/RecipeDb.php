<?php

namespace OCA\Cookbook\Db;

use OCP\DB\QueryBuilder\IQueryBuilder;
use Doctrine\DBAL\Types\Type;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\IDBConnection;
use OCP\AppFramework\Db\DoesNotExistException;

class RecipeDb {

    private const DB_TABLE_RECIPES = 'cookbook_names';
    private const DB_TABLE_KEYWORDS = 'cookbook_keywords';
    private const DB_TABLE_CATEGORIES = 'cookbook_categories';
    
    private $db;
    
    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     * @deprecated
     * @todo Why deprecated?
     */
    public function findRecipeById(int $id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from(self::DB_TABLE_RECIPES)
            ->where('recipe_id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

        $cursor = $qb->execute();
        $row = $cursor->fetch();
        $cursor->closeCursor();

        if($row === false)
        {
            throw new DoesNotExistException("Recipe with $id was not found in database.");
        }
        
        $ret = [];
        $ret['name'] = $row['name'];
        $ret['id'] = $row['recipe_id'];
        
        return $ret;
    }
    
    public function deleteRecipeById(int $id) {
        $qb = $this->db->getQueryBuilder();

        $qb->delete(self::DB_TABLE_RECIPES)
            ->where('recipe_id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        
        $qb->execute();
        
        $qb->delete(self::DB_TABLE_KEYWORDS)
            ->where('recipe_id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);
        
        $qb->delete(self::DB_TABLE_CATEGORIES)
            ->where('recipe_id = :id');
        $qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

        $qb->execute();
    }
    
    public function findAllRecipes(string $user_id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
            ->from(self::DB_TABLE_RECIPES, 'r')
            ->where('user_id = :user')
            ->orderBy('r.name');
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();

        $result = $this->sortRecipes($result);
        
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
    
    private function sortRecipes(array $recipes): array
    {
        usort($recipes, function($a, $b){
            return strcasecmp($a['name'], $b['name']);
        });
        
        return $recipes;
    }

    public function findAllKeywords(string $user_id) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('k.name')
			->selectAlias($qb->createFunction('COUNT(k.recipe_id)'), 'recipe_count')
            ->from(self::DB_TABLE_KEYWORDS, 'k')
            ->where('user_id = :user AND k.name != \'\'')
            ->groupBy('k.name')
            ->orderBy('k.name');
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();
        
        $result = $this->sortRecipes($result);
		
        $result = array_unique($result, SORT_REGULAR);
        $result = array_filter($result);
		
        return $result;
    }
    
    public function findAllCategories(string $user_id) {
        $qb = $this->db->getQueryBuilder();

        // Get all named categories
        $qb->select('c.name')
			->selectAlias($qb->createFunction('COUNT(c.recipe_id)'), 'recipe_count')
            ->from(self::DB_TABLE_CATEGORIES, 'c')
            ->where('user_id = :user')
            ->groupBy('c.name')
            ->orderBy('c.name');
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();
        
        $qb = $this->db->getQueryBuilder();
        
        // Get count of recipes without category
        $qb->select($qb->createFunction('COUNT(1) as cnt'))
            ->from(self::DB_TABLE_RECIPES, 'r')
            ->leftJoin(
                'r',
                self::DB_TABLE_CATEGORIES,
                'c',
                $qb->expr()->andX(
                    'r.user_id = c.user_id',
                    'r.recipe_id = c.recipe_id'
                    )
                )
            ->where(
                $qb->expr()->eq('r.user_id', $qb->expr()->literal($user_id)),
                $qb->expr()->isNull('c.name')
                );
		
        $cursor = $qb->execute();
        $row = $cursor->fetch();
        $cursor->closeCursor();
        
        $result[] = array(
            'name' => '*',
            'recipe_count' => $row['cnt']
        );

        $result = array_unique($result, SORT_REGULAR);
        $result = array_filter($result);
		
        return $result;
    }
    
    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     *
     * Using '_' as a placeholder for recipes w/o category
     */
    public function getRecipesByCategory(string $category, string $user_id) {
        $qb = $this->db->getQueryBuilder();

        if ($category != '_')
        {
            $qb->select(['r.recipe_id', 'r.name'])
                ->from(self::DB_TABLE_CATEGORIES, 'k')
                ->where('k.name = :category')
                ->andWhere('k.user_id = :user')
                ->setParameter('category', $category, TYPE::STRING)
                ->setParameter('user', $user_id, TYPE::STRING);
            
            $qb->join('k', self::DB_TABLE_RECIPES, 'r', 'k.recipe_id = r.recipe_id');

            $qb->groupBy(['r.name', 'r.recipe_id']);
            $qb->orderBy('r.name');            
        }
        else
        {

            $qb->select(['r.recipe_id', 'r.name'])
            ->from(self::DB_TABLE_RECIPES, 'r')
            ->leftJoin(
                'r',
                self::DB_TABLE_CATEGORIES,
                'c',
                $qb->expr()->andX(
                    'r.user_id = c.user_id',
                    'r.recipe_id = c.recipe_id'
                    )
                )
            ->where(
                $qb->expr()->eq('r.user_id', $qb->expr()->literal($user_id)),
                $qb->expr()->isNull('c.name')
                );
        }

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
            ->from(self::DB_TABLE_RECIPES, 'r');
        
        $qb->leftJoin('r', self::DB_TABLE_KEYWORDS, 'k', 'k.recipe_id = r.recipe_id');
        $qb->leftJoin('r', self::DB_TABLE_CATEGORIES, 'c', 'r.recipe_id = c.recipe_id');
        
        $paramIdx = 1;
        $params = [];
        $types = [];
        
        foreach ($keywords as $keyword)
        {
            $lowerKeyword = strtolower($keyword);
            
            $qb->orWhere("LOWER(k.name) LIKE :keyword$paramIdx");
            $qb->orWhere("LOWER(r.name) LIKE :keyword$paramIdx");
            $qb->orWhere("LOWER(c.name) LIKE :keyword$paramIdx");
            
            $params["keyword$paramIdx"] = "%$lowerKeyword%";
            $types["keyword$paramIdx"] = Type::STRING;
            $paramIdx++;
            
        }

        $qb->andWhere('r.user_id = :user');

        $qb->setParameters($params, $types);
        $qb->setParameter('user', $user_id, TYPE::STRING);

        $qb->groupBy(['r.name', 'r.recipe_id']);
        $qb->orderBy('r.name');

        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();

        return $this->unique($result);
    }
    
    /**
     * @param string $user_id
     * @deprecated
     */
    public function emptySearchIndex(string $user_id) {
        $qb = $this->db->getQueryBuilder();
        
        $qb->delete(self::DB_TABLE_RECIPES)
            ->where('user_id = :user')
            ->orWhere('user_id = :empty');
        $qb->setParameter('user', $user_id, TYPE::STRING);
        $qb->setParameter('empty', 'empty', TYPE::STRING);
        
        $qb->execute();
        
        $qb->delete(self::DB_TABLE_KEYWORDS)
            ->where('user_id = :user')
            ->orWhere('user_id = :empty');
        $qb->setParameter('user', $user_id, TYPE::STRING);
        $qb->setParameter('empty', 'empty', TYPE::STRING);
        
        $qb->delete(self::DB_TABLE_CATEGORIES)
            ->where('user_id = :user')
            ->orWhere('user_id = :empty');
        $qb->setParameter('user', $user_id, TYPE::STRING);
        $qb->setParameter('empty', 'empty', TYPE::STRING);
        
        $qb->execute();
    }

    private function isRecipeEmpty($json) {}

    /**
     * @param array $ids
     */
    public function deleteRecipes(array $ids, string $userId)
    {
        if(!is_array($ids) || empty($ids))
            return;
        
        foreach ($ids as $id)
        {
            // Remove category
            $this->removeCategoryOfRecipe($id, $userId);
            
            // Remove all keywords
            $keywords = $this->getKeywordsOfRecipe($id, $userId);
            $pairs = array_map(function ($kw) use ($id) {
                return array('recipeId' => $id, 'name' => $kw);
            }, $keywords);
            $this->removeKeywordPairs($pairs, $userId);
        }
        
        $qb = $this->db->getQueryBuilder();
        
        $qb->delete(self::DB_TABLE_RECIPES);
        
        foreach ($ids as $id)
            $qb->orWhere(
                $qb->expr()->andX(
                    "recipe_id = $id",
                    $qb->expr()->eq("user_id", $qb->expr()->literal($userId))
                    ));
        
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
    
    public function updateRecipes(array $recipes, string $userId)
    {
        if(!is_array($recipes) || empty($recipes))
            return;
        
        $qb = $this->db->getQueryBuilder();
        
        foreach ($recipes as $recipe)
        {
            $qb->update(self::DB_TABLE_RECIPES)
                ->where('recipe_id = :id', 'user_id = :uid');
            
            $literal = array();
            $literal['name'] = $qb->expr()->literal($recipe['name'], IQueryBuilder::PARAM_STR);
            $qb->set('name', $literal['name']);
            
            $qb->setParameter('id', $recipe['id']);
            $qb->setParameter('uid', $userId);
            
            $qb->execute();
        }
    }
    
    public function getKeywordsOfRecipe(int $recipeId, string $userId)
    {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('name')
            ->from(self::DB_TABLE_KEYWORDS)
            ->where('recipe_id = :rid', 'user_id = :uid');
        
        $qb->setParameter('rid', $recipeId);
        $qb->setParameter('uid', $userId);
        
        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        $cursor->closeCursor();
        
        $ret = array_map(function ($row){
            $r = $row['name'];
            return $r;
        }, $result);
        
        return $ret;
    }
    
    public function getCategoryOfRecipe(int $recipeId, string $userId)
    {
        $qb = $this->db->getQueryBuilder();
        
        $qb->select('name')
        ->from(self::DB_TABLE_CATEGORIES)
        ->where('recipe_id = :rid', 'user_id = :uid');
        
        $qb->setParameter('rid', $recipeId);
        $qb->setParameter('uid', $userId);
        
        $cursor = $qb->execute();
        $result = $cursor->fetch();
        $cursor->closeCursor();
        
        return $result['name'];
    }
    
    public function updateCategoryOfRecipe(int $recipeId, string $categoryName, string $userId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->update(self::DB_TABLE_CATEGORIES)
            ->where('recipe_id = :rid', 'user_id = :user');
        $qb->set('name', $qb->expr()->literal($categoryName, IQueryBuilder::PARAM_STR));
        $qb->setParameter('rid', $recipeId, Type::INTEGER);
        $qb->setParameter('user', $userId, Type::STRING);
        $qb->execute();
    }
    
    public function addCategoryOfRecipe(int $recipeId, string $categoryName, string $userId)
    {
        // NOTE: We're using * as a placeholder for no category
        if(empty($categoryName)) {
            $categoryName = '*';
        }
//         else if(is_array($json['recipeCategory']))
//         {
//             $json['recipeCategory'] = reset($json['recipeCategory']);
//         }
        
        $qb = $this->db->getQueryBuilder();
        $qb->insert(self::DB_TABLE_CATEGORIES)
            ->values(array('recipe_id' => ':rid', 'name' => ':name', 'user_id' => ':user'));
        $qb->setParameter('rid', $recipeId, Type::INTEGER);
        $qb->setParameter('name', $categoryName, Type::STRING);
        $qb->setParameter('user', $userId, Type::STRING);
        
        try {
            $qb->execute();
        }
        catch (\Exception $e)
        {
            // Category didn't meet restrictions, skip it
        }
    }
    
    public function removeCategoryOfRecipe(int $recipeId, string $userId)
    {
        $qb = $this->db->getQueryBuilder();
        $qb->delete(self::DB_TABLE_CATEGORIES)
            ->where('recipe_id = :rid', 'user_id = :user');
        $qb->setParameter('rid', $recipeId, Type::INTEGER);
        $qb->setParameter('user', $userId, Type::STRING);
        $qb->execute();
    }
    
    public function addKeywordPairs(array $pairs, string $userId)
    {
        if(!is_array($pairs) || empty($pairs))
            return;
        
        $qb = $this->db->getQueryBuilder();
        $qb->insert(self::DB_TABLE_KEYWORDS)
            ->values(array('recipe_id' => ':rid', 'name' => ':name', 'user_id' => ':user'));
        $qb->setParameter('user', $userId, Type::STRING);
        
        foreach ($pairs as $p)
        {
            $qb->setParameter('rid', $p['recipeId'], Type::INTEGER);
            $qb->setParameter('name', $p['name'], Type::STRING);
            
            try
            {
                $qb->execute();
            }
            catch(\Exception $ex)
            {
                // The insertion of a keywaord might conflict with the requirements. Skip it.
            }
        }
    }
    
    public function removeKeywordPairs(array $pairs, string $userId)
    {
        if(!is_array($pairs) || empty($pairs))
            return;
        
        $qb = $this->db->getQueryBuilder();
        $qb->delete(self::DB_TABLE_KEYWORDS);
        
        foreach ($pairs as $p)
        {
            $qb->orWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('user_id', $qb->expr()->literal($userId)),
                    $qb->expr()->eq('recipe_id', $qb->expr()->literal($p['recipeId'])),
                    $qb->expr()->eq('name', $qb->expr()->literal($p['name']))
                    )
                );
        }
        
        $qb->execute();
    }
}

?>
