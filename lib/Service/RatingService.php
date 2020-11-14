<?php

namespace OCA\Cookbook\Service;

use OCP\IL10N;
use OCA\Cookbook\JsonService;
use OCP\IDBConnection;
use OCP\DB\QueryBuilder\IQueryBuilder;

class RatingService
{
    
    /**
     * @var RecipeService
     */
    private $recipeService;
    
    /**
     * @var IL10N
     */
    private $l;
    
    /**
     * @var JsonService
     */
    private $jsonService;
    
    /**
     * @var IDBConnection
     */
    private $db;
    
    /**
     * The name of the rating field
     */
    private const CONTENT_RATING = 'contentRating';
    
    /**
     * Plain default constructor
     * 
     * @param RecipeService $recipeService
     * @param JsonService $jsonService
     * @param IL10N $l10n
     * @param IDBConnection $db
     */
    public function __construct(RecipeService $recipeService, JsonService $jsonService,
        IL10N $l10n, IDBConnection $db)
    {
        $this->recipeService = $recipeService;
        $this->jsonService = $jsonService;
        $this->l = $l10n;
        $this->db = $db;
    }
    
    /**
     * Remove a rating from a recipe
     * @param int $recipeId The recipe to remove the rating from
     * @param string $userId The user of the rating that should be removed
     * @throws \Exception If either the JSON is invalid or the recipe was not found.
     */
    public function removeRating(int $recipeId, string $userId)
    {
        $json = $this->recipeService->getRecipeById($recipeId);
        
        if($json === null)
        {
            throw new \Exception($this->l->t('No matching recipe was found.'));
        }
        
        $json = $this->canonicalizeRatings($json);
        
        $idx = $this->searchForRating($json, $userId);
        
        if($idx >= 0)
        {
            // We found the rating
            unset($json[self::CONTENT_RATING][$idx]);
            
            $json = $this->updateAggregateRating($json);
            $this->updateDatabase($userId, $recipeId, $json['aggregateRating']);
            
            // Undo our canonicalization
            $json = $this->uncanonicalizeRatings($json);
            
            // Save the file on disk
            $recipeFile = $this->recipeService->getRecipeFileByFolderId($recipeId);
            $recipeFile->putContent(json_encode($json));
            
            $recipeFile->getParent()->touch();
        }
    }
    
    /**
     * Add a rating to a recipe
     * @param int $recipeId The recipe to add the rating to
     * @param string $userId The user who gave the rating
     * @param int $rating The rating value
     * @throws \Exception If either the JSON is invalid or the recipe was not found.
     */
    public function addRating(int $recipeId, string $userId, int $rating) : void
    {
        $json = $this->recipeService->getRecipeById($recipeId);
        
        if($json === null)
        {
            throw new \Exception($this->l->t('No matching recipe was found.'));
        }
        
        $json = $this->canonicalizeRatings($json);
        
        $idx = $this->searchForRating($json, $userId);
        
        if($idx == -1)
        {
            // Add a new rating
            $json[self::CONTENT_RATING][] = $this->createRating($rating, $userId);
        }
        else
        {
            // Replace the corresponding rating
            $json[self::CONTENT_RATING][$idx] = $this->createRating($rating, $userId);
        }
        
        $this->updateAggregateRating($json);
        $this->updateDatabase($userId, $recipeId, $json['aggregateRating']);
        
        // Undo our canonicalization
        $json = $this->uncanonicalizeRatings($json);
        
        // Save the file on disk
        $recipeFile = $this->recipeService->getRecipeFileByFolderId($recipeId);
        $recipeFile->putContent(json_encode($json));
        
        $recipeFile->getParent()->touch();
    }
    
    /**
     * Create a schema.org [Rating object](https://schema.org/Rating)
     * 
     * @param int $rating The numerical rating value
     * @param string $userId The user id of the rating
     * @return array The Rating object generated as an array 
     */
    private function createRating(int $rating, string $userId) : array
    {
        $ret = [];
        
        $ret['@type'] = 'Rating';
        $ret['ratingValue'] = $rating;
        
        $ret['author'] = array(
            '@type' => 'Person',
            'identifier' => $userId
        );
        
        return $ret;
    }
    
    /**
     * Update the aggregate rating of the structure.
     * 
     * The ratings **must** be canonicalized.
     * 
     * @param array $json The object to parse
     * @return array The updated structure
     */
    private function updateAggregateRating(array $json) : array
    {
        $count = count($json[self::CONTENT_RATING]);
        
        $json['aggregateRating'] = array(
            '@type' => 'AggregateRating',
            'ratingCount' => $count
        );
        
        if($count > 0)
        {
            // We have some ratings
            $min = $max = -1;
            $sum = 0;
            
            foreach($json[self::CONTENT_RATING] as $rating)
            {
                if($this->jsonService->isSchemaObject($rating, 'Rating') &&
                    $this->jsonService->hasProperty($rating, 'ratingValue'))
                {
                    // Shortcut for the value of the rating
                    $rv = $rating['ratingValue'];
                    
                    $min = ($min == -1) ? $rv : min([ $min, $rv ]);
                    $max = ($max == -1) ? $rv : max([ $max, $rv ]);
                    $sum += $rv;
                }
            }
            
            $json['aggregateRating']['bestRating'] = $max;
            $json['aggregateRating']['worstRating'] = $min;
            $json['aggregateRating']['ratingValue'] = (float) $sum / $count;
        }
        
        return $json;
    }
    
    /**
     * Update the ratings in the database
     * @param string $userId The owner of the recipe
     * @param int $recipeId The id or the recipe
     * @param array $rating The aggregate rating array as a AggregareRating schema.org object
     * @deprecated This should be moved to a DB helper class
     */
    private function updateDatabase(string $userId, int $recipeId, array $rating)
    {
        
        $qb = $this->db->getQueryBuilder();
        
        $qb->delete('cookbook_ratings')
            ->where(['recipe_id = :rid', 'user_id = :uid']);
        
        $qb->setParameter('uid', $userId, IQueryBuilder::PARAM_STR);
        $qb->setParameter('rid', $recipeId, IQueryBuilder::PARAM_INT);
        
        $qb->execute();
        
        if($rating['ratingCount'] > 0)
        {
            $qb = $this->db->getQueryBuilder();
            
            $qb ->insert('cookbook_ratings')
                ->values([
                    'recipe_id' => ':rid',
                    'user_id' => ':uid',
                    'rating' => ':rating'
                ]);
            
            $qb->setParameters([
                'rid' => $recipeId,
                'uid' => $userId,
                'rating' => $rating['ratingValue']
            ], [
                IQueryBuilder::PARAM_INT,
                IQueryBuilder::PARAM_STR,
                IQueryBuilder::PARAM_STR
            ]);
            
            $qb->execute();
        }
    }
    
    /**
     * Ensure that the contentRating property exists and is an array.
     * 
     * By the schema.org standard, the rating might be either
     * - non-existent
     * - an empty array
     * - a single string
     * - a single Rating object
     * - an array consisting of strings and/or Rating objects.
     * 
     * For simpler processing this method eensures that the property
     * exists and is always an array.
     * The individual ratings are entries in this recipe.
     * So, the number of entries in the property represent the count of ratings.
     * 
     * @param string $json The JSON string to be canonicalized
     * @return string The canonical JSON as string
     */
    private function canonicalizeRatings(string $json) : string
    {
        if(! isset($json[self::CONTENT_RATING]))
        {
            // Ensure there is at leasst an empty array 
            $json[self::CONTENT_RATING] = [];
        }
        
        if(is_string($json[self::CONTENT_RATING]))
        {
            // If there is only a single rating in form of text, put it into an array
            $json[self::CONTENT_RATING] = array($json[self::CONTENT_RATING]);
        }
        
        // We have for sure an array now.
        
        if($this->jsonService->isSchemaObject($json[self::CONTENT_RATING]))
        {
            // We do have an object as rating. Put it into a nested array for iterating
            $json[self::CONTENT_RATING] = array(
                $json[self::CONTENT_RATING]
            );
        }
        
        return $json;
    }
    
    /**
     * Undo the changes by canonicalizeRatings
     * 
     * This makes the JSON again compatible with the schema.org standard.
     * 
     * @param string $json The JSON in canconical form
     * @return string The standard conforming JSON as string
     */
    private function uncanonicalizeRatings(string $json) : string
    {
        if(count($json[self::CONTENT_RATING]) == 0)
        {
            unset($json[self::CONTENT_RATING]);
        }
        else if(count($json[self::CONTENT_RATING]) == 1)
        {
            // Move the only entry to the top level.
            $json[self::CONTENT_RATING] = $json[self::CONTENT_RATING][0];
        }
        
        return $json;
    }
    
    /**
     * Search all ratings for a rating with the given user id.
     * 
     * Please note that the JSON data to be searched **must** be canonicalized!
     * 
     * @param string $json The data of the recipe to look for ratings
     * @param string $userId The user to look for
     * @throws \Exception If an invalid type of rating was found in the JSON
     * @return int The index of the rating in the array or -1 if no rating for the user was found.
     */
    private function searchForRating(string $json, string $userId) : int
    {
        foreach($json[self::CONTENT_RATING] as $key => $val)
        {
            if(is_string($val))
            {
                // Simple text rating found. We have no knowledge who wrote it. Skipping here
                // XXX Do something useful?
                continue;
            }
            
            if(! $this->jsonService->isSchemaObject($val, 'Rating'))
            {
                // Some illegal object was found in the array.
                throw new \Exception($this->l->t('Invalid type for rating found.'));
            }
            
            if(! $this->jsonService->hasProperty($val, 'author') || 
                ! $this->jsonService->isSchemaObject($val['author'], 'Person'))
            {
                // We have no clue about the author. Skipping here.
                // XXX Do something useful?
                continue;
            }
            
            if(! $this->jsonService->hasProperty($val['author'], 'identifier'))
            {
                // Without id of the author we cannot match it
                continue;
            }
            
            if($val['author']['identifier'] === $userId)
            {
                return $key;
            }
        }
        
        // Nothing was found
        return -1;
    }
    
}
