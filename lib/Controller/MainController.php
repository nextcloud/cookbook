<?php

namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\RecipeService;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCP\AppFramework\Http\JSONResponse;

class MainController extends Controller {
	protected $appName;

	/**
	 * @var RecipeService
	 */
	private $service;
	/**
	 * @var DbCacheService
	 */
	private $dbCacheService;
	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;
	
	/**
	 * @var RestParameterParser
	 */
	private $restParser;

	public function __construct(string $AppName, IRequest $request, RecipeService $recipeService, DbCacheService $dbCacheService, IURLGenerator $urlGenerator, RestParameterParser $restParser) {
		parent::__construct($AppName, $request);

		$this->service = $recipeService;
		$this->urlGenerator = $urlGenerator;
		$this->appName = $AppName;
		$this->dbCacheService = $dbCacheService;
		$this->restParser = $restParser;
	}

	/**
	 * Load the start page of the app.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		try {
			// Check if the user folder can be accessed
			$this->service->getFolderForUser();
		} catch (UserFolderNotWritableException $ex) {
			return new TemplateResponse($this->appName, 'invalid_guest');
		}
		
		$this->dbCacheService->triggerCheck();

		return new TemplateResponse($this->appName, 'index');  // templates/index.php
	}
	
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @return DataResponse
	 */
	public function getApiVersion(): DataResponse {
		$response = [
			'cookbook_version' => [0, 8, 4], /* VERSION_TAG do not change this line manually */
			'api_version' => [
				'epoch' => 0,
				'major' => 0,
				'minor' => 2
			]
		];
		return new DataResponse($response, 200, ['Content-Type' => 'application/json']);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function categories() {
		$this->dbCacheService->triggerCheck();
		
		$categories = $this->service->getAllCategoriesInSearchIndex();
		return new DataResponse($categories, 200, ['Content-Type' => 'application/json']);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function keywords() {
		$this->dbCacheService->triggerCheck();
		
		$keywords = $this->service->getAllKeywordsInSearchIndex();
		return new DataResponse($keywords, 200, ['Content-Type' => 'application/json']);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @deprecated
	 */
	public function home() {
		$this->dbCacheService->triggerCheck();
		
		try {
			$recipes = $this->service->getAllRecipesInSearchIndex();

			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['image_url'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb',
						't' => $this->service->getRecipeMTime($recipe['recipe_id'])
					]
				);
			}

			$response = new TemplateResponse($this->appName, 'content/search', ['recipes' => $recipes]);
			$response->renderAs('blank');

			return $response;
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @deprecated
	 */
	public function error() {
		$this->dbCacheService->triggerCheck();
		
		$response = new TemplateResponse($this->appName, 'navigation/error');
		$response->renderAs('blank');

		return $response;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function search($query) {
		$this->dbCacheService->triggerCheck();
		
		$query = urldecode($query);
		try {
			$recipes = $this->service->findRecipesInSearchIndex($query);

			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb',
						't' => $this->service->getRecipeMTime($recipe['recipe_id'])
					]
				);
				$recipes[$i]['imagePlaceholderUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb16'
					]
				);
			}

			return new DataResponse($recipes, 200, ['Content-Type' => 'application/json']);
			// TODO: Remove obsolete code below when this is ready
			$response = new TemplateResponse($this->appName, 'content/search', ['query' => $query, 'recipes' => $recipes]);
			$response->renderAs('blank');

			return $response;
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function category($category) {
		$this->dbCacheService->triggerCheck();
		
		$category = urldecode($category);
		try {
			$recipes = $this->service->getRecipesByCategory($category);
			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb',
						't' => $this->service->getRecipeMTime($recipe['recipe_id'])
					]
				);
				$recipes[$i]['imagePlaceholderUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb16'
					]
				);
			}

			return new DataResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function categoryUpdate($category) {
		$this->dbCacheService->triggerCheck();

		$json = $this->restParser->getParameters();
		if (!$json || !isset($json['name']) || !$json['name']) {
			return new DataResponse('New category name not found in data', 400);
		}

		$category = urldecode($category);
		try {
			$recipes = $this->service->getRecipesByCategory($category);
			foreach ($recipes as $recipe) {
				$r = $this->service->getRecipeById($recipe['recipe_id']);
				$r['recipeCategory'] = $json['name'];
				$this->service->addRecipe($r);
			}
			// Update cache
			$this->dbCacheService->updateCache();

			return new DataResponse($json['name'], Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function tags($keywords) {
		$this->dbCacheService->triggerCheck();
		$keywords = urldecode($keywords);

		try {
			$recipes = $this->service->getRecipesByKeywords($keywords);
			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb',
						't' => $this->service->getRecipeMTime($recipe['recipe_id'])
					]
				);
				$recipes[$i]['imagePlaceholderUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb16'
					]
				);
			}

			return new DataResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			error_log($e->getMessage());
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @deprecated
	 */
	public function recipe($id) {
		$this->dbCacheService->triggerCheck();
		
		try {
			$recipe = $this->service->getRecipeById($id);
			$recipe['image_url'] = $this->urlGenerator->linkToRoute(
				'cookbook.recipe.image',
				[
					'id' => $id,
					'size' => 'full',
					't' => $recipe['dateModified']
				]
			);
			$recipe['id'] = $id;
			$recipe['print_image'] = $this->service->getPrintImage();
			$response = new TemplateResponse($this->appName, 'content/recipe_vue', $recipe);
			$response->renderAs('blank');

			return $response;
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @deprecated
	 */
	public function create() {
		$this->dbCacheService->triggerCheck();
		
		try {
			$recipe = [];

			$response = new TemplateResponse($this->appName, 'content/edit', $recipe);
			$response->renderAs('blank');

			return $response;
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function import() {
		$this->dbCacheService->triggerCheck();
		
		$data = $this->restParser->getParameters();
		
		if (!isset($data['url'])) {
			return new DataResponse('Field "url" is required', 400);
		}

		try {
			$recipe_file = $this->service->downloadRecipe($data['url']);
			$recipe_json = $this->service->parseRecipeFile($recipe_file);
			$this->dbCacheService->addRecipe($recipe_file);

			return new DataResponse($recipe_json, Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (RecipeExistsException $ex) {
			$json = [
				'msg' => $ex->getMessage(),
				'line' => $ex->getLine(),
				'file' => $ex->getFile(),
			];
			return new JSONResponse($json, Http::STATUS_CONFLICT);
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 400);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function new() {
		$this->dbCacheService->triggerCheck();
		
		try {
			$recipe_data = $this->restParser->getParameters();
			$file = $this->service->addRecipe($recipe_data);
			$this->dbCacheService->addRecipe($file);

			return new DataResponse($file->getParent()->getId());
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @deprecated
	 */
	public function edit($id) {
		$this->dbCacheService->triggerCheck();
		
		try {
			$recipe = [];

			if ($id !== null) {
				$recipe = $this->service->getRecipeById($id);

				if (!$recipe) {
					throw new \Exception('Recipe ' . $id . ' not found');
				}

				$recipe['id'] = $id;
			}

			$response = new TemplateResponse($this->appName, 'content/edit', $recipe);
			$response->renderAs('blank');

			return $response;
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function update($id) {
		$this->dbCacheService->triggerCheck();
		
		try {
			$recipe_data = $this->restParser->getParameters();

			$recipe_data['id'] = $id;

			$file = $this->service->addRecipe($recipe_data);
			$this->dbCacheService->addRecipe($file);
			
			return new DataResponse($id);
		} catch (\Exception $e) {
			return new DataResponse($e->getMessage(), 500);
		}
	}
}
