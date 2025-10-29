<?php

namespace OCA\Cookbook\Controller\Implementation;

use Exception;
use OCA\Cookbook\Exception\InvalidDownloadURLException;
use OCA\Cookbook\Exception\InvalidJSONFileException;
use OCA\Cookbook\Exception\NoRecipeNameGivenException;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCA\Cookbook\Helper\AcceptHeaderParsingHelper;
use OCA\Cookbook\Helper\Filter\Output\RecipeJSONOutputFilter;
use OCA\Cookbook\Helper\Filter\Output\RecipeStubFilter;
use OCA\Cookbook\Helper\RestParameterParser;
use OCA\Cookbook\Service\DbCacheService;
use OCA\Cookbook\Service\RecipeService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;

class RecipeImplementation {
	/** @var RecipeService */
	private $service;
	/** @var DbCacheService */
	private $dbCacheService;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var RestParameterParser */
	private $restParser;
	/** @var RecipeJSONOutputFilter */
	private $outputFilter;
	/** @var RecipeStubFilter */
	private $stubFilter;
	/** @var AcceptHeaderParsingHelper */
	private $acceptHeaderParser;
	/** @var IRequest */
	private $request;
	/** @var IL10N */
	private $l;
	/** @var LoggerInterface */
	private $logger;


	public function __construct(
		IRequest $request,
		RecipeService $recipeService,
		DbCacheService $dbCacheService,
		IURLGenerator $iURLGenerator,
		RestParameterParser $restParameterParser,
		RecipeJSONOutputFilter $recipeJSONOutputFilter,
		RecipeStubFilter $stubFilter,
		AcceptHeaderParsingHelper $acceptHeaderParsingHelper,
		IL10N $iL10N,
		LoggerInterface $logger,
		private string $userId,
	) {
		$this->request = $request;
		$this->service = $recipeService;
		$this->dbCacheService = $dbCacheService;
		$this->urlGenerator = $iURLGenerator;
		$this->restParser = $restParameterParser;
		$this->outputFilter = $recipeJSONOutputFilter;
		$this->stubFilter = $stubFilter;
		$this->acceptHeaderParser = $acceptHeaderParsingHelper;
		$this->l = $iL10N;
		$this->logger = $logger;
	}

	/**
	 * List all recipes as stubs
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

			$recipes[$i] = $this->stubFilter->apply($recipes[$i]);
		}

		return new JSONResponse($recipes, Http::STATUS_OK);
	}

	/**
	 * Fetch a single recipe
	 *
	 * @param int $id
	 * @return JSONResponse
	 */
	public function show($id) {
		$this->dbCacheService->triggerCheck();

		$json = $this->service->getRecipeById($id);

		if ($json === null) {
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
	 * @param $id The id of the recipe in question
	 * @return JSONResponse
	 * @todo Parameter id is never used. Fix that
	 */
	public function update($id) {
		$this->dbCacheService->triggerCheck();

		$recipeData = $this->restParser->getParameters();
		try {
			$file = $this->service->addRecipe($recipeData);
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

		try {
			$this->dbCacheService->addRecipe($file);
		} catch (InvalidJSONFileException $ex) {

			try {
				$this->service->deleteRecipe($file->getParent()->getId());
			} catch (Exception $ex) {
				$this->logger->warning('Cannot remove file after failed parsing: {ex}', ['ex' => $ex]);
			}

			$json = [
				'msg' => $ex->getMessage(),
				'file' => $ex->getFile(),
				'line' => $ex->getLine(),
			];

			return new JSONResponse($json, Http::STATUS_UNPROCESSABLE_ENTITY);
		}

		return new JSONResponse($file->getParent()->getId(), Http::STATUS_OK);
	}

	/**
	 * Create a new recipe.
	 *
	 * @return JSONResponse
	 */
	public function create() {
		$this->dbCacheService->triggerCheck();

		$recipeData = $this->restParser->getParameters();
		try {
			$file = $this->service->addRecipe($recipeData);
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

		try {
			$this->dbCacheService->addRecipe($file);
		} catch (InvalidJSONFileException $ex) {

			try {
				$this->service->deleteRecipe($file->getParent()->getId());
			} catch (Exception $ex) {
				$this->logger->warning('Cannot remove file after failed parsing: {ex}', ['ex' => $ex]);
			}

			$json = [
				'msg' => $ex->getMessage(),
				'file' => $ex->getFile(),
				'line' => $ex->getLine(),
			];

			return new JSONResponse($json, Http::STATUS_UNPROCESSABLE_ENTITY);
		}

		return new JSONResponse($file->getParent()->getId(), Http::STATUS_OK);
	}

	/**
	 * Remove a recipe
	 *
	 * @param int $id The ifd of the recipe in question
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
	 * Get the image associated with a recipe
	 *
	 * @param $id The id of the recipe
	 * @return JSONResponse|FileDisplayResponse|DataDisplayResponse
	 */
	public function image($id) {
		$this->dbCacheService->triggerCheck();

		$acceptHeader = $this->request->getHeader('Accept');
		$acceptedExtensions = $this->acceptHeaderParser->parseHeader($acceptHeader);

		try {
			$file = $this->service->getRecipeImageFileByFolderId($id, $_GET['size'] ?? 'full');

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
				$file = file_get_contents(dirname(__FILE__) . '/../../../img/recipe.svg');

				return new DataDisplayResponse($file, Http::STATUS_OK, ['Content-Type' => 'image/svg+xml']);
			}
		}
	}

	/**
	 * @todo Move this code into a dedicated class and service
	 */
	private function isDownloadUrlValid(string $url): bool {
		$urlValid = filter_var($url, FILTER_VALIDATE_URL);
		if ($urlValid === false) {
			$this->logger->info('Download URL is not a valid URL: {url}', ['url' => $url]);
			return false;
		}

		$parsedUrl = parse_url($url);

		$scheme = $parsedUrl['scheme'] ?? '';
		$allowedSchemes = ['http', 'https'];
		if (!in_array($scheme, $allowedSchemes, true)) {
			$this->logger->info('Download URL uses invalid scheme: {scheme}', ['scheme' => $scheme]);
			return false;
		}

		$host = $parsedUrl['host'] ?? '';
		if (empty($host)) {
			$this->logger->info('Download URL does not have a valid host: {url}', ['url' => $url]);
			return false;
		}

		if ($host === 'localhost') {
			$this->logger->info('Download URL host is localhost, which is not allowed.');
			return false;
		}

		if (filter_var($host, FILTER_VALIDATE_IP)) {
			$this->logger->info('Download URL host is an IP address, which is not allowed: {host}', ['host' => $host]);
			return false;
		}

		return true;
	}

	/**
	 * Trigger the import of a recipe.
	 *
	 * The URL is extracted from the request directly.
	 */
	public function import() {
		$this->dbCacheService->triggerCheck();

		$data = $this->restParser->getParameters();

		if (!isset($data['url'])) {
			return new JSONResponse('Field "url" is required', 400);
		}

		if (! $this->isDownloadUrlValid($data['url'])) {
			$this->logger->warning('Attempt to download recipe by user {user} from invalid URL: {url}', ['url' => $data['url'], 'user' => $this->userId]);
			// throw new InvalidDownloadURLException($this->l->t('The provided URL is not allowed.'));
			$json = [
				'msg' => $this->l->t('The provided URL is not allowed.'),
			];
			return new JSONResponse($json['msg'], 400);
		}

		try {
			$recipe_file = $this->service->downloadRecipe($data['url']);
			$recipe_json = $this->service->parseRecipeFile($recipe_file);

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

		try {
			$this->dbCacheService->addRecipe($recipe_file);
		} catch (InvalidJSONFileException $ex) {

			try {
				$this->service->deleteRecipe($recipe_file->getParent()->getId());
			} catch (Exception $ex) {
				$this->logger->warning('Cannot remove file after failed parsing: {ex}', ['ex' => $ex]);
			}

			return new DataResponse($ex->getMessage(), 400);
		}

		return new JSONResponse($recipe_json, Http::STATUS_OK);
	}

	/**
	 * Search for a recipe
	 *
	 * @param string $query The query to search for
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

				$recipes[$i] = $this->stubFilter->apply($recipes[$i]);
			}

			return new JSONResponse($recipes, 200, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 500);
		}
	}

	/**
	 * Get all recipes in a category
	 *
	 * @param string $category The category to filter the recipes by
	 * @return JSONResponse
	 */
	public function getAllInCategory($category) {
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

				$recipes[$i] = $this->stubFilter->apply($recipes[$i]);
			}

			return new JSONResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			return new JSONResponse($e->getMessage(), 500);
		}
	}



	/**
	 * Get all recipes with a tag associated
	 *
	 * The filtering is done such that a recipe is in the result if any keyword is attached.
	 *
	 * @param string $keywords The keywords to look for
	 * @return JSONResponse
	 */
	public function getAllWithTags($keywords) {
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

				$recipes[$i] = $this->stubFilter->apply($recipes[$i]);
			}

			return new JSONResponse($recipes, Http::STATUS_OK, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			// error_log($e->getMessage());
			return new JSONResponse($e->getMessage(), 500);
		}
	}
}
