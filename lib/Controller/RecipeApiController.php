<?php

namespace OCA\Cookbook\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCA\Cookbook\Exception\NoRecipeNameGivenException;

class RecipeApiController extends ApiController {
	protected $appName;

	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var RestParameterParser */
	private $restParser;
	/** @var UserFolderHelper */
	private $userFolder;
	/** @var RecipeJSONOutputFilter */
	private $outputFilter;
	/** @var AcceptHeaderParsingHelper */
	private $acceptHeaderParser;
	/** @var IL10N */
	private $l;

	public function __construct(
		string $AppName,
		IRequest $request
	) {
		parent::__construct($AppName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function index() {
		$this->dbCacheService->triggerCheck();

		if (empty($_GET['keywords'])) {
			$recipes = $this->service->getAllRecipesInSearchIndex();
		} else {
			$recipes = $this->service->findRecipesInSearchIndex(isset($_GET['keywords']) ? $_GET['keywords'] : '');
		}
		foreach ($recipes as $i => $recipe) {
			$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb']);
			$recipes[$i]['imagePlaceholderUrl'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $recipe['recipe_id'], 'size' => 'thumb16']);
		}
		return new JSONResponse($recipes, Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param int $id
	 * @return JSONResponse
	 */
	public function show($id) {
		$this->dbCacheService->triggerCheck();

		$json = $this->service->getRecipeById($id);

		if (null === $json) {
			return new JSONResponse($id, Http::STATUS_NOT_FOUND);
		}

		$json['printImage'] = $this->service->getPrintImage();
		$json['imageUrl'] = $this->urlGenerator->linkToRoute('cookbook.recipe.image', ['id' => $json['id'], 'size' => 'full']);

		$json = $this->outputFilter->filter($json);

		return new JSONResponse($json, Http::STATUS_OK);
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
		$this->dbCacheService->triggerCheck();

		$recipeData = $this->restParser->getParameters();
		try {
			$file = $this->service->addRecipe($recipeData);
		} catch (NoRecipeNameGivenException $ex) {
			$json = [
				'msg' => $ex->getMessage(),
				'file' => $ex->getFile(),
				'line' => $ex->getLine(),
			];
			return new JSONResponse($json, Http::STATUS_UNPROCESSABLE_ENTITY);
		}
		$this->dbCacheService->addRecipe($file);

		return new JSONResponse($file->getParent()->getId(), Http::STATUS_OK);
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
		$this->dbCacheService->triggerCheck();

		$recipeData = $this->restParser->getParameters();
		try {
			$file = $this->service->addRecipe($recipeData);
			$this->dbCacheService->addRecipe($file);

			return new JSONResponse($file->getParent()->getId(), Http::STATUS_OK);
		} catch (RecipeExistsException $ex) {
			$json = [
				'msg' => $ex->getMessage(),
				'file' => $ex->getFile(),
				'line' => $ex->getLine(),
			];
			return new JSONResponse($json, Http::STATUS_CONFLICT);
		} catch (NoRecipeNameGivenException $ex) {
			$json = [
				'msg' => $ex->getMessage(),
				'file' => $ex->getFile(),
				'line' => $ex->getLine(),
			];
			return new JSONResponse($json, Http::STATUS_UNPROCESSABLE_ENTITY);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param int $id
	 * @return JSONResponse
	 */
	public function destroy($id) {
		$this->dbCacheService->triggerCheck();

		try {
			$this->service->deleteRecipe($id);
			return new JSONResponse('Recipe ' . $id . ' deleted successfully', Http::STATUS_OK);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 502);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param $id
	 * @return JSONResponse|FileDisplayResponse|DataDisplayResponse
	 */
	public function image($id) {
		$this->dbCacheService->triggerCheck();

		$acceptHeader = $this->request->getHeader('Accept');
		$acceptedExtensions = $this->acceptHeaderParser->parseHeader($acceptHeader);

		$size = isset($_GET['size']) ? $_GET['size'] : null;

		try {
			$file = $this->service->getRecipeImageFileByFolderId($id, $size);

			return new FileDisplayResponse($file, Http::STATUS_OK, ['Content-Type' => 'image/jpeg', 'Cache-Control' => 'public, max-age=604800']);
		} catch (\Exception $e) {
			if (array_search('svg', $acceptedExtensions, true) === false) {
				// We may not serve a SVG image. Tell the client about the missing image.
				$json = [
					'msg' => $this->l->t('No image with the matching MIME type was found on the server.'),
				];
				return new JSONResponse($json, Http::STATUS_NOT_ACCEPTABLE);
			} else {
				// The client accepts the SVG file. Send it.
				$file = file_get_contents(dirname(__FILE__) . '/../../img/recipe.svg');

				return new DataDisplayResponse($file, Http::STATUS_OK, ['Content-Type' => 'image/svg+xml']);
			}
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 */
	public function import() {
		$this->dbCacheService->triggerCheck();

		$data = $this->restParser->getParameters();

		if (!isset($data['url'])) {
			return new JSONResponse('Field "url" is required', 400);
		}

		try {
			$recipe_file = $this->service->downloadRecipe($data['url']);
			$recipe_json = $this->service->parseRecipeFile($recipe_file);
			$this->dbCacheService->addRecipe($recipe_file);

			return new JSONResponse($recipe_json, Http::STATUS_OK);
		} catch (RecipeExistsException $ex) {
			$json = [
				'msg' => $ex->getMessage(),
				'line' => $ex->getLine(),
				'file' => $ex->getFile(),
			];
			return new JSONResponse($json, Http::STATUS_CONFLICT);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 400);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param string $query
	 * @return JSONResponse
	 */
	public function search($query) {
		$this->dbCacheService->triggerCheck();

		$query = urldecode($query);
		try {
			$recipes = $this->service->findRecipesInSearchIndex($query);

			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb',
						't' => $this->service->getRecipeMTime($recipe['recipe_id'])
					]
				);
				$recipes[$i]['imagePlaceholderUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb16'
					]
				);
			}

			return new JSONResponse($recipes, 200, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 500);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param string $category
	 * @return JSONResponse
	 */
	public function category($category) {
		$this->dbCacheService->triggerCheck();

		$category = urldecode($category);
		try {
			$recipes = $this->service->getRecipesByCategory($category);
			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb',
						't' => $this->service->getRecipeMTime($recipe['recipe_id'])
					]
				);
				$recipes[$i]['imagePlaceholderUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb16'
					]
				);
			}

			return new JSONResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 500);
		}
	}



	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 * @param string $keywords
	 * @return JSONResponse
	 */
	public function tags($keywords) {
		$this->dbCacheService->triggerCheck();
		$keywords = urldecode($keywords);

		try {
			$recipes = $this->service->getRecipesByKeywords($keywords);
			foreach ($recipes as $i => $recipe) {
				$recipes[$i]['imageUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb',
						't' => $this->service->getRecipeMTime($recipe['recipe_id'])
					]
				);
				$recipes[$i]['imagePlaceholderUrl'] = $this->urlGenerator->linkToRoute(
					'cookbook.recipe.image',
					[
						'id' => $recipe['recipe_id'],
						'size' => 'thumb16'
					]
				);
			}

			return new JSONResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			// error_log($e->getMessage());
			return new JSONResponse($e->getMessage(), 500);
		}
	}
}
