<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\ConfigImplementation;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\CORS;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ConfigApiController extends ApiController {
	/** @var ConfigImplementation */
	private $implementation;

	public function __construct(
		string $AppName,
		IRequest $request,
		ConfigImplementation $configImplementation,
	) {
		parent::__construct($AppName, $request);

		$this->implementation = $configImplementation;
	}

	/**
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[CORS]
	public function list() {
		return $this->implementation->list();
	}

	/**
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[CORS]
	public function config() {
		return $this->implementation->config();
	}

	/**
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[CORS]
	public function reindex() {
		return $this->implementation->reindex();
	}
}
