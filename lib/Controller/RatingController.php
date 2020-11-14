<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RatingService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

/**
 * A controller that allows altering of the ratings of a recipe
 * 
 * @author Christian Wolf <githun@christianwolf.email>
 *
 */
class RatingController extends Controller
{
    
    /**
     * @var DbCacheService
     */
    private $dbCacheService;
    
    /**
     * @var RatingService
     */
    private $ratingService;
    
    /**
     * @var string
     */
    private $userId;
    
    public function __construct(
        ?string $UserId, DbCacheService $dbCacheService, RatingService $ratingService)
    {
        $this->dbCacheService = $dbCacheService;
        $this->ratingService = $ratingService;
        $this->userId = $UserId;
    }
    
    /**
     * Add or update a rating for the current user to a recipe
     * @param int $id The id of the recipe to be rated
     */
    public function save($id)
    {
        $this->dbCacheService->triggerCheck();
        
        $data = [];
        parse_str(file_get_contents('php://input'), $data);
        
        $this->ratingService->addRating((int) $id, $this->userId, $data);
        
        return new DataResponse([], Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }
    
    /**
     * Remove a rating from a recipe for the currently logged in user 
     * @param int $id The id of the recipe to be altered
     */
    public function remove($id)
    {
        $this->dbCacheService->triggerCheck();
        $this->ratingService->removeRating($id, $this->userId);
        
        return new DataResponse([], Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }
    
}
