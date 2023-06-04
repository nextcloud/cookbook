<?php

namespace OCA\Cookbook\Service;

use Exception;
use OCA\Cookbook\Db\RecipeDb;
use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Exception\ImportException;
use OCA\Cookbook\Exception\NoRecipeNameGivenException;
use OCA\Cookbook\Exception\RecipeExistsException;
use OCA\Cookbook\Exception\UserFolderNotWritableException;
use OCA\Cookbook\Helper\FileSystem\RecipeNameHelper;
use OCA\Cookbook\Helper\Filter\JSONFilter;
use OCA\Cookbook\Helper\ImageService\ImageSize;
use OCA\Cookbook\Helper\UserConfigHelper;
use OCA\Cookbook\Helper\UserFolderHelper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IL10N;
use OCP\Image;
use OCP\PreConditionNotMetException;
use Psr\Log\LoggerInterface;

/**
 * Main service class for the cookbook app.
 *
 * @package OCA\Cookbook\Service
 */
class RecipeService {
	private $root;
	private $user_id;
	private $db;
	private $il10n;
	/**
	 * @var UserFolderHelper
	 */
	private $userFolder;
	private $logger;

	/**
	 * @var HtmlDownloadService
	 */
	private $htmlDownloadService;

	/**
	 * @var RecipeExtractionService
	 */
	private $recipeExtractionService;

	/**
	 * @var UserConfigHelper
	 */
	private $userConfigHelper;

	/**
	 * @var ImageService
	 */
	private $imageService;
	/** @var RecipeNameHelper */
	private $recipeNameHelper;

	/** @var JSONFilter */
	private $jsonFilter;

	public function __construct(
		?string $UserId,
		IRootFolder $root,
		RecipeDb $db,
		UserConfigHelper $userConfigHelper,
		UserFolderHelper $userFolder,
		ImageService $imageService,
		RecipeNameHelper $recipeNameHelper,
		IL10N $il10n,
		LoggerInterface $logger,
		HtmlDownloadService $downloadService,
		RecipeExtractionService $extractionService,
		JSONFilter $jsonFilter
	) {
		$this->user_id = $UserId;
		$this->root = $root;
		$this->db = $db;
		$this->il10n = $il10n;
		$this->userFolder = $userFolder;
		$this->logger = $logger;
		$this->userConfigHelper = $userConfigHelper;
		$this->imageService = $imageService;
		$this->recipeNameHelper = $recipeNameHelper;
		$this->htmlDownloadService = $downloadService;
		$this->recipeExtractionService = $extractionService;
		$this->jsonFilter = $jsonFilter;
	}

	/**
	 * Get a recipe by its folder id.
	 *
	 * @param int $id
	 *
	 * @return ?array
	 */
	public function getRecipeById(int $id) {
		$file = $this->getRecipeFileByFolderId($id);

		if (!$file) {
			return null;
		}

		return $this->parseRecipeFile($file);
	}

	/**
	 * Get a recipe's modification time by its folder id.
	 *
	 * @param int $id
	 */
	public function getRecipeMTime(int $id): ?int {
		$file = $this->getRecipeFileByFolderId($id);

		if (!$file) {
			return null;
		}

		return $file->getMTime();
	}

	/**
	 * Returns a recipe file by folder id
	 *
	 * @param int $id
	 *
	 * @return File|null
	 */
	public function getRecipeFileByFolderId(int $id) {
		$user_folder = $this->userFolder->getFolder();
		$recipe_folder = $user_folder->getById($id);

		if (count($recipe_folder) <= 0) {
			return null;
		}

		$recipe_folder = $recipe_folder[0];

		if ($recipe_folder instanceof Folder === false) {
			return null;
		}

		foreach ($recipe_folder->getDirectoryListing() as $file) {
			if ($this->isRecipeFile($file)) {
				return $file;
			}
		}

		return null;
	}

	/**
	 * Checks the fields of a recipe and standardises the format
	 *
	 * @param array $json
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function checkRecipe(array $json): array {
		if (!$json) {
			throw new Exception('Recipe array was null');
		}

		if (empty($json['name'])) {
			throw new Exception('Field "name" is required');
		}

		return $this->jsonFilter->apply($json);
	}

	/**
	 * @param int $id
	 */
	public function deleteRecipe(int $id) {
		$user_folder = $this->userFolder->getFolder();
		$recipe_folder = $user_folder->getById($id);

		if ($recipe_folder) {
			$recipe_folder[0]->delete();
		}

		$this->db->deleteRecipeById($id);
	}

	/**
	 * @param array $json
	 * @param ?string $importedHtml The HTML file as downloaded if the recipe was imported
	 *
	 * @return File
	 */
	public function addRecipe($json, $importedHtml = null) {
		if (!$json || !isset($json['name']) || !$json['name']) {
			throw new NoRecipeNameGivenException($this->il10n->t('No recipe name was given. A unique name is required to store the recipe.'));
		}

		$now = date(DATE_ISO8601);

		// Sanity check
		$json = $this->checkRecipe($json);

		// Update modification date
		$json['dateModified'] = $now;

		// Create/move recipe folder
		$user_folder = $this->userFolder->getFolder();
		$recipe_folder = null;

		$recipeFolderName = $this->recipeNameHelper->getFolderName($json['name']);

		if (isset($json['id']) && $json['id']) {
			// Recipe already has an id, update it
			$recipe_folder = $user_folder->getById($json['id'])[0];

			$old_path = $recipe_folder->getPath();
			$new_path = dirname($old_path) . '/' . $recipeFolderName;

			// The recipe is being renamed, move the folder
			if ($old_path !== $new_path) {
				if ($user_folder->nodeExists($recipeFolderName)) {
					throw new RecipeExistsException($this->il10n->t('Another recipe with that name already exists'));
				}

				$recipe_folder->move($new_path);
			}

		} else {
			// This is a new recipe, create it
			$json['dateCreated'] = $now;

			if ($user_folder->nodeExists($recipeFolderName)) {
				throw new RecipeExistsException($this->il10n->t('Another recipe with that name already exists'));
			}

			$recipe_folder = $user_folder->newFolder($recipeFolderName);
		}

		// Write JSON file to disk
		$recipe_file = $this->getRecipeFileByFolderId($recipe_folder->getId());

		if (!$recipe_file) {
			$recipe_file = $recipe_folder->newFile('recipe.json');
		}

		// Rename .json file if it's not "recipe.json"
		if ($recipe_file->getName() !== 'recipe.json') {
			$recipe_file->move(str_replace($recipe_file->getName(), 'recipe.json', $recipe_file->getPath()));
		}

		$recipe_file->putContent(json_encode($json));

		if (! is_null($importedHtml)) {
			// We imported a recipe. Save the import html file as a backup
			$importFile = $recipe_folder->newFile('import.html');
			$importFile->putContent($importedHtml);
			$importFile->touch();
		}

		// Download image and generate thumbnail
		$full_image_data = null;

		if (isset($json['image']) && $json['image']) {
			if (strpos($json['image'], 'http') === 0) {
				// The image is a URL
				$json['image'] = str_replace(' ', '%20', $json['image']);
				$full_image_data = file_get_contents($json['image']);

			} else {
				// The image is a local path
				try {
					$full_image_file = $this->root->get('/' . $this->user_id . '/files' . $json['image']);
					$full_image_data = $full_image_file->getContent();
				} catch (NotFoundException $e) {
					$full_image_data = null;
				}
			}

		} else {
			// The image field was empty, remove images in the recipe folder
			$this->imageService->dropImage($recipe_folder);
		}

		// If image data was fetched, write it to disk
		if ($full_image_data) {
			$this->imageService->setImageData($recipe_folder, $full_image_data);
		}

		// Write .nomedia file to avoid gallery indexing
		if (!$recipe_folder->nodeExists('.nomedia')) {
			$recipe_folder->newFile('.nomedia');
		}

		// Make sure the directory has been marked as changed
		$recipe_folder->touch();

		return $recipe_file;
	}

	/**
	 * Download a recipe from a url and store it in the files
	 *
	 * @param string $url The recipe URL
	 * @throws Exception
	 * @return File
	 */
	public function downloadRecipe(string $url): File {
		$this->htmlDownloadService->downloadRecipe($url);

		try {
			$json = $this->recipeExtractionService->parse($this->htmlDownloadService->getDom(), $url);
			$importedHtml = $this->htmlDownloadService->getDom()->saveHTML();
		} catch (HtmlParsingException $ex) {
			throw new ImportException($ex->getMessage(), null, $ex);
		}

		$json = $this->checkRecipe($json);

		if (!$json) {
			$this->logger->error('Importing parsers resulted in null recipe.' .
				'This is most probably a bug. Please report.');
			throw new ImportException($this->il10n->t('No recipe data found. This is a bug'));
		}

		$json['url'] = $url;

		return $this->addRecipe($json, $importedHtml);
	}

	/**
	 * @return array
	 */
	public function getRecipeFiles() {
		$user_folder = $this->userFolder->getFolder();
		$recipe_folders = $user_folder->getDirectoryListing();
		$recipe_files = [];

		foreach ($recipe_folders as $recipe_folder) {
			$recipe_file = $this->getRecipeFileByFolderId($recipe_folder->getId());

			if (!$recipe_file) {
				continue;
			}

			$recipe_files[] = $recipe_file;
		}

		return $recipe_files;
	}

	/**
	 * Updates the search index (no more) and migrate file structure
	 * @deprecated
	 */
	public function updateSearchIndex() {
		try {
			$this->migrateFolderStructure();
		} catch (UserFolderNotWritableException $ex) {
			// Ignore migration if not permitted.
			$this->logger->warning("Cannot migrate cookbook file structure as not permitted.");
			throw $ex;
		}
	}

	private function migrateFolderStructure() {
		// Remove old cache folder if needed
		$legacy_cache_path = '/cookbook/cache';

		if ($this->root->nodeExists($legacy_cache_path)) {
			$this->root->get($legacy_cache_path)->delete();
		}

		// Restructure files if needed
		$user_folder = $this->userFolder->getFolder();

		foreach ($user_folder->getDirectoryListing() as $node) {
			// Move JSON files from the user directory into its own folder
			if ($this->isRecipeFile($node)) {
				$recipe_name = str_replace('.json', '', $node->getName());

				$node->move($node->getPath() . '_tmp');

				$recipe_folder = $user_folder->newFolder($recipe_name);

				$node->move($recipe_folder->getPath() . '/recipe.json');

			} elseif ($node instanceof Folder && strpos($node->getName(), '.json')) {
				// Rename folders with .json extensions (this was likely caused by a migration bug)
				$node->move(str_replace('.json', '', $node->getPath()));
			}
		}
	}

	/**
	 * Gets all keywords from the index
	 *
	 * @return array
	 */
	public function getAllKeywordsInSearchIndex() {
		return $this->db->findAllKeywords($this->user_id);
	}

	/**
	 * Gets all categories from the index
	 *
	 * @return array
	 */
	public function getAllCategoriesInSearchIndex() {
		return $this->db->findAllCategories($this->user_id);
	}



	/** Adds modification and creation date to each recipe in the list
	 *
	 * @param array $recipes
	 */
	private function addDatesToRecipes(array &$recipes) {
		foreach ($recipes as $i => $recipe) {
			if (! array_key_exists('dateCreated', $recipe) || ! array_key_exists('dateModified', $recipe)) {
				$r = $this->getRecipeById($recipe['recipe_id']);
				$recipes[$i]['dateCreated'] = $r['dateCreated'];
				$recipes[$i]['dateModified'] = $r['dateModified'];
			}
		}
	}

	/**
	 * Gets all recipes from the index
	 *
	 * @return array
	 */
	public function getAllRecipesInSearchIndex(): array {
		$recipes = $this->db->findAllRecipes($this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * Get all recipes of a certain category
	 *
	 * @param string $category
	 *
	 * @return array
	 */
	public function getRecipesByCategory($category): array {
		$recipes = $this->db->getRecipesByCategory($category, $this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * Get all recipes containing all of the keywords.
	 *
	 * @param string $keywords Keywords/tags as a comma-separated string.
	 *
	 * @return array
	 * @throws DoesNotExistException
	 */
	public function getRecipesByKeywords($keywords): array {
		$recipes = $this->db->getRecipesByKeywords($keywords, $this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * Search for recipes by keywords
	 *
	 * @param string $keywords_string
	 *
	 * @return array
	 *
	 * @throws DoesNotExistException
	 *
	 */
	public function findRecipesInSearchIndex(string $keywords_string): array {
		$keywords_string = strtolower($keywords_string);
		$keywords_array = [];
		preg_match_all('/[^ ,]+/', $keywords_string, $keywords_array);

		if (sizeof($keywords_array) > 0) {
			$keywords_array = $keywords_array[0];
		}

		$recipes = $this->db->findRecipes($keywords_array, $this->user_id);
		$this->addDatesToRecipes($recipes);
		return $recipes;
	}

	/**
	 * @param int $interval
	 * @throws PreConditionNotMetException
	 */
	public function setSearchIndexUpdateInterval(int $interval) {
		$this->userConfigHelper->setUpdateInterval($interval);
	}

	/**
	 * @param bool $printImage
	 * @throws PreConditionNotMetException
	 */
	public function setPrintImage(bool $printImage) {
		$this->userConfigHelper->setPrintImage($printImage);
	}

	/**
	 * Should image be printed with the recipe
	 * @return bool
	 */
	public function getPrintImage() {
		return $this->userConfigHelper->getPrintImage();
	}

	/**
	 * Sets which info blocks are displayed next to the recipe
	 * @param array<string, bool> keys: info block ids, values: display state
	 */
	public function setVisibleInfoBlocks(array $visibleInfoBlocks) {
		$this->userConfigHelper->setVisibleInfoBlocks($visibleInfoBlocks);
	}

	/**
	 * Determines which info blocks are displayed next to the recipe
	 * @return array<string, bool> keys: info block ids, values: display state
	 */
	public function getVisibleInfoBlocks(): array {
		return $this->userConfigHelper->getVisibleInfoBlocks();
	}

	/**
	 * Get recipe file contents as an array
	 *
	 * @param File $file
	 */
	public function parseRecipeFile($file): ?array {
		if (!$file) {
			return null;
		}

		$json = json_decode($file->getContent(), true);

		if (!$json) {
			return null;
		}

		$json['id'] = $file->getParent()->getId();


		if (!array_key_exists('dateCreated', $json) && method_exists($file, 'getCreationTime')) {
			$json['dateCreated'] = $file->getCreationTime();
		}
		if (!array_key_exists('dateModified', $json)) {
			$json['dateModified'] = $file->getMTime();
		}

		return $this->checkRecipe($json);
	}

	/**
	 * Gets the image file for a recipe
	 *
	 * @param int $id
	 * @param string $size
	 *
	 * @return File
	 */
	public function getRecipeImageFileByFolderId($id, $size = 'thumb'): File {
		$recipe_folders = $this->root->getById($id);
		if (count($recipe_folders) < 1) {
			throw new Exception($this->il10n->t('Recipe with ID %d not found.', [$id]));
		}
		$recipe_folder = $recipe_folders[0];

		// TODO: Check that file is really an image
		switch ($size) {
			case 'full':
				return $this->imageService->getImageAsFile($recipe_folder);
			case 'thumb':
				return $this->imageService->getThumbnailAsFile($recipe_folder, ImageSize::THUMBNAIL);
			case 'thumb16':
				return $this->imageService->getThumbnailAsFile($recipe_folder, ImageSize::MINI_THUMBNAIL);
			default:
				throw new Exception($this->il10n->t('Image size "%s" is not recognized.', [$size]));
		}
	}

	/**
	 * Test if file is a recipe
	 *
	 * @param File $file
	 *
	 * @return bool
	 */
	private function isRecipeFile($file) {
		$allowedExtensions = ['json'];

		if ($file->getType() !== 'file') {
			return false;
		}

		$ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
		$iext = strtolower($ext);

		if (!in_array($iext, $allowedExtensions)) {
			return false;
		}

		return true;
	}
}
