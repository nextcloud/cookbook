<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\ConfigImplementation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ConfigController extends Controller {
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
	public function list() {
		return $this->implementation->list();
	}

	/**
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function config() {
		return $this->implementation->config();
	}

	/**
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function reindex() {
		return $this->implementation->reindex();
	}
}
