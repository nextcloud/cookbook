<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\ConfigImplementation;
use OCP\IRequest;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;

class ConfigApiController extends ApiController {
	/** @var ConfigImplementation */
	private $implementation;

	public function __construct(
		$AppName,
		IRequest $request,
		ConfigImplementation $configImplementation
	) {
		parent::__construct($AppName, $request);

		$this->implementation = $configImplementation;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @return JSONResponse
	 */
	public function list() {
		return $this->implementation->list();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @return JSONResponse
	 */
	public function config() {
		return $this->implementation->config();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @return JSONResponse
	 */
	public function reindex() {
		return $this->implementation->reindex();
	}
}
