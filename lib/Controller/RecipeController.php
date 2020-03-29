<?php

namespace OCA\Cookbook\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;

use OCA\Cookbook\Service\RecipeService;
use OCP\IURLGenerator;

class RecipeController extends Controller
{
    /**
     * @var RecipeService
     */
    private $service;
    /**
     * @var IURLGenerator
     */
    private $urlGenerator;

    public function __construct($AppName, IRequest $request, IURLGenerator $urlGenerator, RecipeService $recipeService)
    {
        parent::__construct($AppName, $request);

        $this->service = $recipeService;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        if (empty($_GET['keywords'])) {
            $recipes = $this->service->getAllRecipesInSearchIndex();
        } else {
            $recipes = $this->service->findRecipesInSearchIndex(isset($_GET['keywords']) ? $_GET['keywords'] : '');
        }
        foreach ($recipes as $i => $recipe) {
            $mtime = $this->imageModificationTime($recipe['recipe_id'], 'thumb');
            $recipes[$i]['image_url'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb', 't' => $mtime]);
        }
        return new DataResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }

    public function imageModificationTime($id, $size)
    {
        $path_candidates = [
            $this->service->getRecipeImageFileByFolderId($id, $size),
            dirname(__FILE__) . '/../../img/recipe-' . $size . '.jpg'
        ];

        foreach($path_candidates as $path) {
            if(file_exists($path)) {
                return filemtime($path);
            }
        }

        return -1;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param int $id
     * @return DataResponse
     */
    public function show($id)
    {
        $json = $this->service->getRecipeById($id);

        if (null === $json) {
            return new DataResponse($id, Http::STATUS_NOT_FOUND, ['Content-Type' => 'application/json']);
        }
        return new DataResponse($json, Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Update an existing recipe.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param $id
     *
     * @return DataResponse
     */
    public function update($id)
    {
        $recipeData = [];
        parse_str(file_get_contents("php://input"), $recipeData);
        $file = $this->service->addRecipe($recipeData);

        return new DataResponse($file->getParent()->getId(), Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Create a new recipe.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param $id
     *
     * @return DataResponse
     */
    public function create()
    {
        $recipeData = $_POST;
        $file = $this->service->addRecipe($recipeData);

        return new DataResponse($file->getParent()->getId(), Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param int $id
     * @return DataResponse
     */
    public function destroy($id)
    {
        try {
            $this->service->deleteRecipe($id);
            return new DataResponse('Recipe ' . $_GET['id'] . ' deleted successfully', Http::STATUS_OK);
        } catch (\Exception $e) {
            return new DataResponse($e->getMessage(), 502);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param $id
     * @return DataResponse|FileDisplayResponse
     */
    public function image($id)
    {
        $size = isset($_GET['size']) ? $_GET['size'] : null;
        $headers = [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800' // cache for one week
        ];

        try {
            $file = $this->service->getRecipeImageFileByFolderId($id, $size);

            return new FileDisplayResponse($file, Http::STATUS_OK, $headers);
        
        } catch (\Exception $e) {
            $file = file_get_contents(dirname(__FILE__) . '/../../img/recipe-' . $size . '.jpg');
            
            return new DataDisplayResponse($file, Http::STATUS_OK, $headers);
        }
    }
}
