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
    private $dbFiles;
    
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
        $this->dbFiles = $this->fetchDbInformations();
        
        $this->resetFields();
        $this->compareLists();
        
        $this->applyDbChanges();
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
    
    private function fetchDbInformations()
    {
        $dbResult = $this->db->findAllRecipes($this->userId);
        
        $ret = array();
        
        foreach ($dbResult as $row)
        {
            // TODO Create an Entity from DB row better in DB file
            $obj = array();
            $obj['name'] = $row['name'];
            $obj['id'] = $row['recipe_id'];
            
            $ret[$obj['id']] = $obj;
        }
        
        return $ret;
    }
    
    private function compareLists()
    {
        foreach (array_keys($this->jsonFiles) as $id)
        {
            if(array_key_exists($id, $this->dbFiles))
            {
                // The file was at least in the database
                
                if( ! $this->isDbEntryUpToDate($id))
                {
                    // An update is needed
                    $this->updatedRecipes[] = $id;
                }
                
                // Remove from array for later removal of old recipes
                unset($this->dbFiles[$id]);
            }
            else
            {
                // The file needs to be inserted in the database
                $this->newRecipes[] = $id;
            }
        }
        
        // Any remining recipe in dbFiles is to be removed
        $this->obsoleteRecipes = array_keys($this->dbFiles);
    }
    
    private function isDbEntryUpToDate($id)
    {
        $dbEntry = $this->dbFiles[$id];
        $fileEntry = $this->jsonFiles[$id];
        
        if($dbEntry['name'] != $fileEntry['name'])
            return false;
        
        return true;
    }
    
    private function applyDbChanges()
    {
        $this->db->deleteRecipes($this->obsoleteRecipes);
        
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
        $this->db->updateRecipes($updatedRecipes);
    }
}
