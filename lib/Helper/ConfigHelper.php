<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;

class ConfigHelper {
	/** @var RecipeService */
	private $recipeService;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var UserFolderHelper */
	private $userFolderHelper;

	public function __construct(
		RecipeService $recipeService,
		DbCacheService $dbCacheService,
		UserFolderHelper $userFolder
	) {
		$this->recipeService = $recipeService;
		$this->dbCacheService = $dbCacheService;
		$this->userFolderHelper = $userFolder;
	}

	public function getConfig(): array {
		return [
			'folder' => $this->userFolderHelper->getPath(),
			'update_interval' => $this->dbCacheService->getSearchIndexUpdateInterval(),
			'print_image' => $this->recipeService->getPrintImage(),
			'visibleInfoBlocks' => $this->recipeService->getVisibleInfoBlocks(),
		];
	}
}
