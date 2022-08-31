<?php

namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\ApiController;
use OCA\Cookbook\Service\RecipeService;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Helper\RestParameterParser;
use OCP\AppFramework\Http\JSONResponse;

class CategoryApiController extends ApiController {
	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var RestParameterParser */
	private $restParser;
	/** @var UserFolderHelper */
	private $userFolder;

	public function __construct(
		$AppName,
		IRequest $request,
		RecipeService $recipeService,
		DbCacheService $dbCacheService,
		RestParameterParser $restParser,
		UserFolderHelper $userFolder
	) {
		parent::__construct($AppName, $request);

		$this->service = $recipeService;
		$this->dbCacheService = $dbCacheService;
		$this->restParser = $restParser;
		$this->userFolder = $userFolder;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function list() {
		$this->dbCacheService->triggerCheck();

		return new JSONResponse([
			'folder' => $this->userFolder->getPath(),
			'update_interval' => $this->dbCacheService->getSearchIndexUpdateInterval(),
			'print_image' => $this->service->getPrintImage(),
		], Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function config() {
		$data = $this->restParser->getParameters();

		if (isset($data['folder'])) {
			$this->userFolder->setPath($data['folder']);
			$this->dbCacheService->updateCache();
		}

		if (isset($data['update_interval'])) {
			$this->service->setSearchIndexUpdateInterval($data['update_interval']);
		}

		if (isset($data['print_image'])) {
			$this->service->setPrintImage((bool)$data['print_image']);
		}

		$this->dbCacheService->triggerCheck();

		return new JSONResponse('OK', Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function reindex() {
		$this->dbCacheService->updateCache();

		return new JSONResponse('Search index rebuilt successfully', Http::STATUS_OK);
	}
}
