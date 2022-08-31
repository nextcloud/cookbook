<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class KeywordApiController extends ApiController {
	protected $appName;

	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;

	public function __construct(
		string $AppName,
		IRequest $request,
		RecipeService $recipeService,
		DbCacheService $dbCacheService
	) {
		parent::__construct($AppName, $request);

		$this->service = $recipeService;
		$this->dbCacheService = $dbCacheService;
	}
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function keywords() {
		$this->dbCacheService->triggerCheck();

		$keywords = $this->service->getAllKeywordsInSearchIndex();
		return new JSONResponse($keywords, 200);
	}
}
