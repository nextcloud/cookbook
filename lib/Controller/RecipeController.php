<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\RecipeImplementation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;

class RecipeController extends Controller {
	/** @var RecipeImplementation */
	private $impl;

	public function __construct(
		$AppName,
		IRequest $request,
		RecipeImplementation $recipeImplementation
	) {
		parent::__construct($AppName, $request);

		$this->impl = $recipeImplementation;
	}

	/**
	 * @NoAdminRequired
	 */
	public function index() {
		return $this->impl->index();
	}

	/**
	 * @NoAdminRequired
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
	 *
	 * @return JSONResponse
	 */
	public function create() {
		return $this->impl->create();
	}

	/**
	 * @NoAdminRequired
	 * @param int $id
	 * @return JSONResponse
	 */
	public function destroy($id) {
		return $this->impl->destroy($id);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @param $id
	 * @return Response
	 */
	public function image($id) {
		return $this->impl->image($id);
	}

	/**
	 * @NoAdminRequired
	 */
	public function import() {
		return $this->impl->import();
	}

	/**
	 * @NoAdminRequired
	 * @param string $query
	 * @return JSONResponse
	 */
	public function search($query) {
		return $this->impl->search($query);
	}

	/**
	 * @NoAdminRequired
	 * @param string $category
	 * @return JSONResponse
	 */
	public function category($category) {
		return $this->impl->getAllInCategory($category);
	}



	/**
	 * @NoAdminRequired
	 * @param string $keywords
	 * @return JSONResponse
	 */
	public function tags($keywords) {
		return $this->impl->getAllWithTags($keywords);
	}
}
