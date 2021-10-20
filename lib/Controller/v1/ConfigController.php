<?php

namespace OCA\Cookbook\Controller\v1;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Cookbook\Service\RecipeService;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Helper\RestParameterParser;

class ConfigController extends Controller {
	/**
	 * @var RecipeService
	 */
	private $service;
	
	/**
	 * @var DbCacheService
	 */
	private $dbCacheService;
	
	/**
	 * @var RestParameterParser
	 */
	private $restParser;

	public function __construct($AppName, IRequest $request, RecipeService $recipeService, DbCacheService $dbCacheService, RestParameterParser $restParser) {
		parent::__construct($AppName, $request);

		$this->service = $recipeService;
		$this->dbCacheService = $dbCacheService;
		$this->restParser = $restParser;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function list() {
		$this->dbCacheService->triggerCheck();
		
		return new DataResponse([
			'folder' => $this->service->getUserFolderPath(),
			'update_interval' => $this->dbCacheService->getSearchIndexUpdateInterval(),
			'print_image' => $this->service->getPrintImage(),
		], Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function config() {
		$data = $this->restParser->getParameters();
		
		if (isset($data['folder'])) {
			$this->service->setUserFolderPath($data['folder']);
			$this->dbCacheService->updateCache();
		}

		if (isset($data['update_interval'])) {
			$this->service->setSearchIndexUpdateInterval($data['update_interval']);
		}

		if (isset($data['print_image'])) {
			$this->service->setPrintImage((bool)$data['print_image']);
		}

		$this->dbCacheService->triggerCheck();
		
		return new DataResponse('OK', Http::STATUS_OK);
	}
	
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function reindex() {
		$this->dbCacheService->updateCache();

		return new DataResponse('Search index rebuilt successfully', Http::STATUS_OK);
	}
}
