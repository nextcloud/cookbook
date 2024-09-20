<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\ConfigImplementation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ConfigController extends Controller {
	/** @var ConfigImplementation */
	private $implementation;

	public function __construct(
		$AppName,
		IRequest $request,
		ConfigImplementation $configImplementation,
	) {
		parent::__construct($AppName, $request);

		$this->implementation = $configImplementation;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function list() {
		return $this->implementation->list();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function config() {
		return $this->implementation->config();
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function reindex() {
		return $this->implementation->reindex();
	}
}
