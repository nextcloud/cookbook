<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class CategoryApiController extends ApiController {
	protected $appName;

	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var RestParameterParser */
	private $restParser;

	public function __construct(
		string $AppName,
		IRequest $request,
		RecipeService $service,
		DbCacheService $dbCacheService,
		RestParameterParser $restParameterParser
	) {
		parent::__construct($AppName, $request);

		$this->appName = $AppName;
		$this->service = $service;
		$this->dbCacheService = $dbCacheService;
		$this->restParser = $restParameterParser;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function categories() {
		$this->dbCacheService->triggerCheck();

		$categories = $this->service->getAllCategoriesInSearchIndex();
		return new JSONResponse($categories, 200);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param JSONResponse $category
	 */
	public function rename($category) {
		$this->dbCacheService->triggerCheck();

		$json = $this->restParser->getParameters();
		if (!$json || !isset($json['name']) || !$json['name']) {
			return new JSONResponse('New category name not found in data', 400);
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

			return new JSONResponse($json['name'], Http::STATUS_OK);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 500);
		}
	}
}
