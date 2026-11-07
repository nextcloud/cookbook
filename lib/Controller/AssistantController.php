<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\AssistantImplementation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class AssistantController extends Controller {
	/** @var AssistantImplementation */
	private $implementation;

	public function __construct(
		string $AppName,
		IRequest $request,
		AssistantImplementation $assistantImplementation,
	) {
		parent::__construct($AppName, $request);

		$this->implementation = $assistantImplementation;
	}

	/**
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function create() {
		return $this->implementation->create();
	}

	/**
	 * @param int $id
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function show($id) {
		return $this->implementation->show($id);
	}

	/**
	 * @param int $id
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function cancel($id) {
		return $this->implementation->cancel($id);
	}
}
