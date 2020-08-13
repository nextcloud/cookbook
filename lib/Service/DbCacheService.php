<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Db\RecipeDb;

class DbCacheService
{
    
    private $userId;
//     var $root;
    
    /**
     * @var RecipeDb
     */
    private $db;
    
    /**
     * @var RecipeService
     */
    private $recipeService;
    
    private $jsonFiles;
    private $dbReceipeFiles;
    private $dbKeywords;
    private $dbCategories;
    
    private $newRecipes;
    private $obsoleteRecipes;
    private $updatedRecipes;
    
    public function __construct(?string $UserId, RecipeDb $db, RecipeService $recipeService)
    {
        $this->userId = $UserId;
//         $this->root = $root;
        $this->db = $db;
        $this->recipeService = $recipeService;
        
    }
    
    public function updateCache()
    {
        $this->jsonFiles = $this->parseJSONFiles();
        $this->dbReceipeFiles = $this->fetchDbRecipeInformations();
        
        $this->resetFields();
        $this->compareReceipeLists();
        
        $this->applyDbReceipeChanges();
        
        $this->fetchDbAssociatedInformations();
        $this->updateCategories();
        $this->updateKeywords();
        
        // FIXME Continue writing
    }
    
    private function resetFields()
    {
        $this->newRecipes = array();
        $this->obsoleteRecipes = array();
        $this->updatedRecipes = array();
    }
    
    private function parseJSONFiles() 
    {
        $ret = array();
        
        $jsonFiles = $this->recipeService->getRecipeFiles();
        foreach ($jsonFiles as $jsonFile)
        {
            // XXX Export of file reading into library/service?
            $json = json_decode($jsonFile->getContent(), true);
            
            // TODO Need to be implemented using Exception
            // if(!$json || !isset($json['name']) || $json['name'] === 'No name') { return; }
            
            $id = (int) $jsonFile->getParent()->getId();
            $json['id'] = $id;
            
            $ret[$id] = $json;
        }
        
        return $ret;
    }
    
    private function fetchDbRecipeInformations()
    {
        $dbResult = $this->db->findAllRecipes($this->userId);
        
        $ret = array();
        
        foreach ($dbResult as $row)
        {
            // TODO Create an Entity from DB row better in DB file
            $id = $row['recipe_id'];
            
            $obj = array();
            $obj['name'] = $row['name'];
            $obj['id'] = $id;
            
            $ret[$id] = $obj;
        }
        
        return $ret;
    }
    
    private function fetchDbAssociatedInformations()
    {
        $recipeIds = array_keys($this->jsonFiles);
        
        $this->dbKeywords = array();
        $this->dbCategories = array();
        
        foreach ($recipeIds as $rid)
        {
            // XXX Enhancement by selecting all keywords/categories and associating in RAM
            $this->dbKeywords[$rid] = $this->db->getKeywordsOfRecipe($rid, $this->userId);
            $this->dbCategories[$rid] = $this->db->getCategoryOfRecipe($rid, $this->userId);
        }
    }
    
    private function compareReceipeLists()
    {
        foreach (array_keys($this->jsonFiles) as $id)
        {
            if(array_key_exists($id, $this->dbReceipeFiles))
            {
                // The file was at least in the database
                
                if( ! $this->isDbEntryUpToDate($id))
                {
                    // An update is needed
                    $this->updatedRecipes[] = $id;
                }
                
                // Remove from array for later removal of old recipes
                unset($this->dbReceipeFiles[$id]);
            }
            else
            {
                // The file needs to be inserted in the database
                $this->newRecipes[] = $id;
            }
        }
        
        // Any remining recipe in dbFiles is to be removed
        $this->obsoleteRecipes = array_keys($this->dbReceipeFiles);
    }
    
//     private function 
    
    private function isDbEntryUpToDate($id)
    {
        $dbEntry = $this->dbReceipeFiles[$id];
        $fileEntry = $this->jsonFiles[$id];
        
        if($dbEntry['name'] != $fileEntry['name'])
            return false;
        
        return true;
    }
    
    private function applyDbReceipeChanges()
    {
        $this->db->deleteRecipes($this->obsoleteRecipes, $this->userId);
        
        $newRecipes = array_map(
            function ($id)
            { 
                return $this->jsonFiles[$id]; 
            },
            $this->newRecipes
        );
        $this->db->insertRecipes($newRecipes, $this->userId);
        
        $updatedRecipes = array_map(
            function ($id)
            {
                return $this->jsonFiles[$id];
            },
            $this->updatedRecipes
        );
        $this->db->updateRecipes($updatedRecipes, $this->userId);
    }
    
    private function updateCategories()
    {
        foreach ($this->jsonFiles as $rid => $json)
        {
            if($this->hasJSONCategory($json))
            {
                // There is a category in the JSON file present.
                
                $category = trim($json['recipeCategory']);
                
                if(isset($this->dbCategories[$rid]))
                {
                    // There is a category present. Update needed?
                    if($this->dbCategories[$rid] != trim($category))
                        $this->db->updateCategoryOfRecipe($rid, $category, $this->userid);
                }
                else
                    $this->db->addCategoryOfRecipe($rid, $category, $this->userId);
            }
            else
            {
                // There is no category in the JSON file present.
                if(isset($this->dbCategories[$rid]))
                    $this->db->removeCategoryOfRecipe($rid, $this->userId);
            }
        }
    }
    
    /**
     * @param array $json
     * @return boolean
     */
    private function hasJSONCategory(array $json)
    {
        return isset($json['recipeCategory']) && strlen(trim($json['recipeCategory'])) > 0;
    }
    
    private function updateKeywords()
    {
        $newPairs = [];
        $obsoletePairs = [];
        
        foreach ($this->jsonFiles as $rid => $json)
        {
            
            $keywords = explode(',', $json['keywords']);
            $keywords = array_map(function ($v) {
                return trim($v);
            }, $keywords);
            $keywords = array_filter($keywords, function ($v) {
                return ! empty($v);
            });
            
            $dbKeywords = $this->dbKeywords[$rid];
            
            $onlyInDb = array_filter($dbKeywords, function ($v) use ($keywords) {
                return empty(array_keys($keywords, $v));
            });
            $onlyInJSON = array_filter($keywords, function ($v) use ($dbKeywords){
                return empty(array_keys($dbKeywords, $v));
            });
            
            $newPairs = array_merge($newPairs, array_map(function ($keyword) use ($rid) {
                return array(
                    'recipeId' => $rid,
                    'name' => $keyword
                );
            }, $onlyInJSON));
            $obsoletePairs = array_merge($obsoletePairs, array_map(function ($keyword) use ($rid) {
                return array(
                    'recipeId' => $rid,
                    'name' => $keyword
                );
            }, $onlyInDb));
        }
        
        $this->db->addKeywordPairs($newPairs, $this->userId);
        $this->db->removeKeywordPairs($obsoletePairs, $this->userId);
    }
}
