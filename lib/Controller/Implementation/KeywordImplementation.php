<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

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
		DbCacheService $dbCacheService,
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
