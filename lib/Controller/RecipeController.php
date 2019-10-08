<?php

namespace OCA\Cookbook\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IDBConnection;
use OCP\Files\IRootFolder;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;

use OCA\Cookbook\Service\RecipeService;
use OCP\IURLGenerator;

class RecipeController extends Controller
{
    private $userId;
    /**
     * @var RecipeService
     */
    private $service;
    /**
     * @var IURLGenerator
     */
    private $urlGenerator;

    public function __construct($AppName, IDBConnection $db, IRootFolder $root, IRequest $request, IConfig $config, $UserId, IURLGenerator $urlGenerator)
    {
        parent::__construct($AppName, $request);
        $this->userId = $UserId;

        $this->service = new RecipeService($root, $UserId, $db, $config);
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
            $recipes[$i]['image_url'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb']);
        }
        return new DataResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function config()
    {
        if (isset($_POST['folder'])) {
            $this->service->setUserFolderPath($_POST['folder']);
            $this->service->rebuildSearchIndex();
        }

        if (isset($_POST['update_interval'])) {
            $this->service->setSearchIndexUpdateInterval($_POST['update_interval']);
        }

        return new DataResponse('OK', Http::STATUS_OK);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function add()
    {
        if (!isset($_POST['url'])) {
            return new DataResponse('Field "url" is required', 400);
        }

        try {
            $recipe_file = $this->service->downloadRecipe($_POST['url']);
            $recipe_json = $this->service->parseRecipeFile($recipe_file);
            return new DataResponse($recipe_json, Http::STATUS_OK, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return new DataResponse($e->getMessage(), 502);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param int $id
     * @return DataResponse
     */
    public function get($id)
    {
        $json = $this->service->getRecipeById($id);

        if (null === $json) {
            return new DataResponse($id, Http::STATUS_NOT_FOUND, ['Content-Type' => 'application/json']);
        }
        return new DataResponse($json, Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param $id
     *
     * @return DataResponse
     */
    public function update($id)
    {
        $recipeData = array();
        parse_str(file_get_contents("php://input"), $recipeData);
        $this->service->deleteRecipe($_GET['id']);
        $file = $this->service->addRecipe($recipeData);

        return new DataResponse($file->getParent()->getId(), Http::STATUS_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param int $id
     * @return DataResponse
     */
    public function delete($id)
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
     */
    public function reindex()
    {
        $this->service->rebuildSearchIndex();

        return new DataResponse('Search index rebuilt successfully', Http::STATUS_OK);
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

        try {
            $file = $this->service->getRecipeImageFileByFolderId($id, $size);

            return new FileDisplayResponse($file, Http::STATUS_OK, ['Content-Type' => 'image/jpeg']);
        } catch (\Exception $e) {
            return new DataResponse($e->getMessage(), Http::STATUS_NOT_FOUND);
        }
    }
}
