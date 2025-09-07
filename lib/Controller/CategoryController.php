<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\CategoryImplementation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class CategoryController extends Controller {
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
	#[NoAdminRequired]
	public function categories() {
		return $this->impl->index();
	}

	/**
	 * @param string $category
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function rename($category) {
		return $this->impl->rename($category);
	}
}
