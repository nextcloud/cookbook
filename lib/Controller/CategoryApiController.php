<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\CategoryImplementation;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class CategoryApiController extends ApiController {
	/** @var CategoryImplementation */
	private $impl;

	public function __construct(
		string $AppName,
		IRequest $request,
		CategoryImplementation $categoryImplementation
	) {
		parent::__construct($AppName, $request);

		$this->impl = $categoryImplementation;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @return JSONResponse
	 */
	public function categories() {
		return $this->impl->index();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param string $category
	 * @return JSONResponse
	 */
	public function rename($category) {
		return $this->impl->rename($category);
	}
}
