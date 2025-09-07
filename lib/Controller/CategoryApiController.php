<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\CategoryImplementation;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\Attribute\CORS;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class CategoryApiController extends ApiController {
	/** @var CategoryImplementation */
	private $impl;

	public function __construct(
		string $AppName,
		IRequest $request,
		CategoryImplementation $categoryImplementation,
	) {
		parent::__construct($AppName, $request);

		$this->impl = $categoryImplementation;
	}

	/**
	 * @return JSONResponse
	 */
	#[NoCSRFRequired]
	#[CORS]
	#[NoAdminRequired]
	public function categories() {
		return $this->impl->index();
	}

	/**
	 * @param string $category
	 * @return JSONResponse
	 */
	#[CORS]
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function rename($category) {
		return $this->impl->rename($category);
	}
}
