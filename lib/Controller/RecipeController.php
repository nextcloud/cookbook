<?php

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Controller\Implementation\RecipeImplementation;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;

class RecipeController extends Controller {
	/** @var RecipeImplementation */
	private $impl;

	public function __construct(
		$AppName,
		IRequest $request,
		RecipeImplementation $recipeImplementation,
	) {
		parent::__construct($AppName, $request);

		$this->impl = $recipeImplementation;
	}

	#[NoAdminRequired]
	public function index() {
		return $this->impl->index();
	}

	/**
	 * @param int $id
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function show($id) {
		return $this->impl->show($id);
	}

	/**
	 * Update an existing recipe.
	 *
	 * @param $id
	 * @return JSONResponse
	 * @todo Parameter id is never used. Fix that
	 */
	#[NoAdminRequired]
	public function update($id) {
		return $this->impl->update($id);
	}

	/**
	 * Create a new recipe.
	 *
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function create() {
		return $this->impl->create();
	}

	/**
	 * @param int $id
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function destroy($id) {
		return $this->impl->destroy($id);
	}

	/**
	 * @param $id
	 * @return Response
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function image($id) {
		return $this->impl->image($id);
	}

	#[NoAdminRequired]
	public function import() {
		return $this->impl->import();
	}

	/**
	 * @param string $query
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function search($query) {
		return $this->impl->search($query);
	}

	/**
	 * @param string $category
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function category($category) {
		return $this->impl->getAllInCategory($category);
	}



	/**
	 * @param string $keywords
	 * @return JSONResponse
	 */
	#[NoAdminRequired]
	public function tags($keywords) {
		return $this->impl->getAllWithTags($keywords);
	}
}
