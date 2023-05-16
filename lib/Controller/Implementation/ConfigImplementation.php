<?php

namespace OCA\Cookbook\Controller\Implementation;

use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

class ConfigImplementation {
	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var RestParameterParser */
	private $restParser;
	/** @var UserFolderHelper */
	private $userFolder;

	public function __construct(
		RecipeService $recipeService,
		DbCacheService $dbCacheService,
		RestParameterParser $restParser,
		UserFolderHelper $userFolder
	) {
		$this->service = $recipeService;
		$this->dbCacheService = $dbCacheService;
		$this->restParser = $restParser;
		$this->userFolder = $userFolder;
	}

	protected const KEY_VISIBLE_INFO_BLOCKS = 'visibleInfoBlocks';

	/**
	 * Get the current configuration of the app
	 *
	 * @return JSONResponse
	 */
	public function list() {
		$this->dbCacheService->triggerCheck();

		return new JSONResponse([
			'folder' => $this->userFolder->getPath(),
			'update_interval' => $this->dbCacheService->getSearchIndexUpdateInterval(),
			'print_image' => $this->service->getPrintImage(),
			self::KEY_VISIBLE_INFO_BLOCKS => $this->service->getVisibleInfoBlocks(),
		], Http::STATUS_OK);
	}

	/**
	 * Store the configuration in the database.
	 *
	 * The value to be stored is extracted from the request directly.
	 *
	 * Note that only those values are stored, that are present in the parameter.
	 * All other configurations are not altered.
	 *
	 * @return JSONResponse
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

		if (isset($data[self::KEY_VISIBLE_INFO_BLOCKS])) {
			$this->service->setVisibleInfoBlocks($data[self::KEY_VISIBLE_INFO_BLOCKS]);
		}

		$this->dbCacheService->triggerCheck();

		return new JSONResponse('OK', Http::STATUS_OK);
	}

	/**
	 * Trigger a reindex/rescan of the current recipe folder.
	 *
	 * @return JSONResponse
	 */
	public function reindex() {
		$this->dbCacheService->updateCache();

		return new JSONResponse('Search index rebuilt successfully', Http::STATUS_OK);
	}
}
