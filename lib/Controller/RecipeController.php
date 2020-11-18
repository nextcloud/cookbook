<?php

namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Controller;

use OCA\Cookbook\Service\RecipeService;
use OCP\IURLGenerator;
use OCA\Cookbook\Service\DbCacheService;

class RecipeController extends Controller {
	/**
	 * @var RecipeService
	 */
	private $service;
	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;
	
	/**
	 * @var DbCacheService
	 */
	private $dbCacheService;

	public function __construct($AppName, IRequest $request, IURLGenerator $urlGenerator, RecipeService $recipeService, DbCacheService $dbCacheService) {
		parent::__construct($AppName, $request);

		$this->service = $recipeService;
		$this->urlGenerator = $urlGenerator;
		$this->dbCacheService = $dbCacheService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$this->dbCacheService->triggerCheck();
		
		if (empty($_GET['keywords'])) {
			$recipes = $this->service->getAllRecipesInSearchIndex();
		} else {
			$recipes = $this->service->findRecipesInSearchIndex(isset($_GET['keywords']) ? $_GET['keywords'] : '');
		}
		foreach ($recipes as $i => $recipe) {
			$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb']);
		}
		return new DataResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param int $id
	 * @return DataResponse
	 */
	public function show($id) {
		$this->dbCacheService->triggerCheck();
		
		$json = $this->service->getRecipeById($id);

		if (null === $json) {
			return new DataResponse($id, Http::STATUS_NOT_FOUND, ['Content-Type' => 'application/json']);
		}

		$json['printImage'] = $this->service->getPrintImage();
		$json['imageUrl'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $json['id'], 'size' => 'full']);
		
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
	public function update($id) {
		$this->dbCacheService->triggerCheck();
		
		$recipeData = [];
		parse_str(file_get_contents("php://input"), $recipeData);
		$file = $this->service->addRecipe($recipeData);
		$this->dbCacheService->addRecipe($file);

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
	public function create() {
		$this->dbCacheService->triggerCheck();
		
		$recipeData = $_POST;
		$file = $this->service->addRecipe($recipeData);
		$this->dbCacheService->addRecipe($file);

		return new DataResponse($file->getParent()->getId(), Http::STATUS_OK, ['Content-Type' => 'application/json']);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param int $id
	 * @return DataResponse
	 */
	public function destroy($id) {
		$this->dbCacheService->triggerCheck();
		
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
	public function image($id) {
		$this->dbCacheService->triggerCheck();
		
		$size = isset($_GET['size']) ? $_GET['size'] : null;

		try {
			$file = $this->service->getRecipeImageFileByFolderId($id, $size);

			return new FileDisplayResponse($file, Http::STATUS_OK, ['Content-Type' => 'image/jpeg', 'Cache-Control' => 'public, max-age=604800']);
		} catch (\Exception $e) {
			$file = file_get_contents(dirname(__FILE__) . '/../../img/recipe-' . $size . '.jpg');

			return new DataDisplayResponse($file, Http::STATUS_OK, ['Content-Type' => 'image/jpeg']);
		}
	}
}
