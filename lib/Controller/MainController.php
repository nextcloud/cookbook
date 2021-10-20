<?php

namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCA\Cookbook\Service\RecipeService;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Exception\UserFolderNotWritableException;

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

	public function __construct(string $AppName, IRequest $request, RecipeService $recipeService, DbCacheService $dbCacheService) {
		parent::__construct($AppName, $request);

		$this->service = $recipeService;
		$this->appName = $AppName;
		$this->dbCacheService = $dbCacheService;
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
			'cookbook_version' => [0, 9, 6], /* VERSION_TAG do not change this line manually */
			'api_version' => [
				'epoch' => 0,
				'major' => 0,
				'minor' => 3
			]
		];
		return new DataResponse($response, 200, ['Content-Type' => 'application/json']);
	}
}
