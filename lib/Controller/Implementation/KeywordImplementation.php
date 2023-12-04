<?php

namespace OCA\Cookbook\Controller\Implementation;

use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http\JSONResponse;

class KeywordImplementation {
	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;

	public function __construct(
		RecipeService $recipeService,
		DbCacheService $dbCacheService
	) {
		$this->service = $recipeService;
		$this->dbCacheService = $dbCacheService;
	}
	/**
	 * List all available keywords.
	 *
	 * @return JSONResponse
	 */
	public function index() {
		$this->dbCacheService->triggerCheck();

		$keywords = $this->service->getAllKeywordsInSearchIndex();
		return new JSONResponse($keywords, 200);
	}
}
