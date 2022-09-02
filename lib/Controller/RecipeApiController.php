<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\RecipeImplementation;
use OCP\IRequest;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\FileDisplayResponse;

class RecipeApiController extends ApiController {
	/** @var RecipeImplementation */
	private $impl;

	public function __construct(
		string $AppName,
		IRequest $request,
		RecipeImplementation $recipeImplementation
	) {
		parent::__construct($AppName, $request);

		$this->impl = $recipeImplementation;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function index() {
		return $this->impl->index();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param int $id
	 * @return JSONResponse
	 */
	public function show($id) {
		return $this->impl->show($id);
	}

	/**
	 * Update an existing recipe.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param $id
	 * @return JSONResponse
	 * @todo Parameter id is never used. Fix that
	 */
	public function update($id) {
		return $this->impl->update($id);
	}

	/**
	 * Create a new recipe.
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * @return JSONResponse
	 */
	public function create() {
		return $this->impl->create();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param int $id
	 * @return JSONResponse
	 */
	public function destroy($id) {
		return $this->impl->destroy($id);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param $id
	 * @return JSONResponse|FileDisplayResponse|DataDisplayResponse
	 */
	public function image($id) {
		return $this->impl->image($id);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function import() {
		return $this->impl->import();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param string $query
	 * @return JSONResponse
	 */
	public function search($query) {
		return $this->impl->search($query);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param string $category
	 * @return JSONResponse
	 */
	public function category($category) {
		return $this->impl->getAllInCategory($category);
	}



	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param string $keywords
	 * @return JSONResponse
	 */
	public function tags($keywords) {
		return $this->impl->getAllWithTags($keywords);
	}
}
