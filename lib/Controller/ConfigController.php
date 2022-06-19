<?php

namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Cookbook\Service\RecipeService;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Helper\UserFolderHelper;

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

	/**
	 * @var UserFolderHelper
	 */
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
	 */
	public function list() {
		$this->dbCacheService->triggerCheck();
		
		return new DataResponse([
			'folder' => $this->userFolder->getPath(),
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
