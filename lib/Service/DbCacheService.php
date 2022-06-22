<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Db\RecipeDb;
use OCP\Files\File;
use OCP\AppFramework\Db\DoesNotExistException;
use OCA\Cookbook\Exception\InvalidJSONFileException;
use OCA\Cookbook\Helper\UserConfigHelper;
use OCP\IL10N;

class DbCacheService {
	private $userId;
	//     var $root;

	/**
	 * @var RecipeDb
	 */
	private $db;

	/**
	 * @var RecipeService
	 */
	private $recipeService;

	/**
	 * @var UserConfigHelper
	 */
	private $userConfigHelper;

	/**
	 * @var IL10N
	 */
	private $l;

	private $jsonFiles;
	private $dbReceipeFiles;
	private $dbKeywords;
	private $dbCategories;

	private $newRecipes;
	private $obsoleteRecipes;
	private $updatedRecipes;

	public function __construct(
			?string $UserId,
			RecipeDb $db,
			RecipeService $recipeService,
			UserConfigHelper $userConfigHelper,
			IL10N $l
		) {
		$this->userId = $UserId;
		$this->db = $db;
		$this->recipeService = $recipeService;
		$this->userConfigHelper = $userConfigHelper;
		$this->l = $l;
	}

	public function updateCache() {
		$this->jsonFiles = $this->parseJSONFiles();
		$this->dbReceipeFiles = $this->fetchDbRecipeInformations();

		$this->carryOutUpdate();
	}

	/**
	 * @param File $recipeFile
	 */
	public function addRecipe(File $recipeFile) {
		try {
			$json = $this->parseJSONFile($recipeFile);
		} catch (InvalidJSONFileException $e) {
			// XXX Put a log message and infor the user of problem.
			return;
		}

		$id = $json['id'];

		$this->jsonFiles = [$id => $json];

		$this->dbReceipeFiles = [];
		try {
			$dbEntry = $this->fetchSingleRecipeDbInformations($id);
			$this->dbReceipeFiles[$id] = $dbEntry;
		} catch (DoesNotExistException $e) {
			// No entry was found, keep the array empty
		}

		$this->carryOutUpdate();
	}

	private function carryOutUpdate() {
		$this->resetFields();
		$this->compareReceipeLists();

		$this->applyDbReceipeChanges();

		$this->fetchDbAssociatedInformations();
		$this->updateCategories();
		$this->updateKeywords();
	}

	private function resetFields() {
		$this->newRecipes = [];
		$this->obsoleteRecipes = [];
		$this->updatedRecipes = [];
	}

	private function parseJSONFiles() {
		$ret = [];

		$jsonFiles = $this->recipeService->getRecipeFiles();
		foreach ($jsonFiles as $jsonFile) {
			try {
				$json = $this->parseJSONFile($jsonFile);
			} catch (InvalidJSONFileException $e) {
				continue;
			}
			$id = $json['id'];

			$ret[$id] = $json;
		}

		return $ret;
	}

	/**
	 * @param File $jsonFile
	 * @throws InvalidJSONFileException
	 * @return array
	 */
	private function parseJSONFile(File $jsonFile): array {
		// XXX Export of file reading into library/service?
		$json = json_decode($jsonFile->getContent(), true);

		if (!$json || !isset($json['name']) || $json['name'] === 'No name') {
			$id = $jsonFile->getParent()->getId();

			throw new InvalidJSONFileException($this->l->t('The JSON file in the folder with id %d does not have a valid name.', [$id]));
		}

		$id = (int) $jsonFile->getParent()->getId();
		$json['id'] = $id;

		return $json;
	}

	private function fetchDbRecipeInformations() {
		$dbResult = $this->db->findAllRecipes($this->userId);

		$ret = [];

		foreach ($dbResult as $row) {
			// XXX Create an Entity from DB row better in DB file
			$id = $row['recipe_id'];

			$obj = [];
			$obj['name'] = $row['name'];
			$obj['id'] = $id;

			$ret[$id] = $obj;
		}

		return $ret;
	}

	/**
	 *
	 * @param int $id
	 * @return array
	 * @throws DoesNotExistException
	 */
	private function fetchSingleRecipeDbInformations(int $id) {
		return $this->db->findRecipeById($id);
	}

	private function fetchDbAssociatedInformations() {
		$recipeIds = array_keys($this->jsonFiles);

		$this->dbKeywords = [];
		$this->dbCategories = [];

		foreach ($recipeIds as $rid) {
			// XXX Enhancement by selecting all keywords/categories and associating in RAM into data structure
			$this->dbKeywords[$rid] = $this->db->getKeywordsOfRecipe($rid, $this->userId);
			$category = $this->db->getCategoryOfRecipe($rid, $this->userId);

			$this->dbCategories[$rid] = $category;
		}
	}

	private function compareReceipeLists() {
		foreach (array_keys($this->jsonFiles) as $id) {
			if (array_key_exists($id, $this->dbReceipeFiles)) {
				// The file was at least in the database

				if (! $this->isDbEntryUpToDate($id)) {
					// An update is needed
					$this->updatedRecipes[] = $id;
				}

				// Remove from array for later removal of old recipes
				unset($this->dbReceipeFiles[$id]);
			} else {
				// The file needs to be inserted in the database
				$this->newRecipes[] = $id;
			}
		}

		// Any remining recipe in dbFiles is to be removed
		$this->obsoleteRecipes = array_keys($this->dbReceipeFiles);
	}

	//     private function

	private function isDbEntryUpToDate($id) {
		$dbEntry = $this->dbReceipeFiles[$id];
		$fileEntry = $this->jsonFiles[$id];

		if ($dbEntry['name'] !== $fileEntry['name']) {
			return false;
		}

		return true;
	}

	private function applyDbReceipeChanges() {
		$this->db->deleteRecipes($this->obsoleteRecipes, $this->userId);

		$newRecipes = array_map(
			function ($id) {
				return $this->jsonFiles[$id];
			},
			$this->newRecipes
		);
		$this->db->insertRecipes($newRecipes, $this->userId);

		$updatedRecipes = array_map(
			function ($id) {
				return $this->jsonFiles[$id];
			},
			$this->updatedRecipes
		);
		$this->db->updateRecipes($updatedRecipes, $this->userId);
	}

	private function updateCategories() {
		foreach ($this->jsonFiles as $rid => $json) {
			if ($this->hasJSONCategory($json)) {
				// There is a category in the JSON file present.

				$category = trim($this->getJSONCategory($json));

				if (isset($this->dbCategories[$rid])) {
					// There is a category present. Update needed?
					if ($this->dbCategories[$rid] !== trim($category)) {
						$this->db->updateCategoryOfRecipe($rid, $category, $this->userId);
					}
				} else {
					$this->db->addCategoryOfRecipe($rid, $category, $this->userId);
				}
			} else {
				// There is no category in the JSON file present.
				if (isset($this->dbCategories[$rid])) {
					$this->db->removeCategoryOfRecipe($rid, $this->userId);
				}
			}
		}
	}

	/**
	 * @param array $json
	 * @return bool
	 */
	private function hasJSONCategory(array $json): bool {
		return ! is_null($this->getJSONCategory($json));
	}

	/**
	 * Get the category of a recipe.
	 *
	 * This will only return the very first category if there are multiple registered.
	 *
	 * @param array $json The recipe
	 * @return string|null The category name of null if no category was found.
	 */
	private function getJSONCategory(array $json): ?string {
		if (!isset($json['recipeCategory'])) {
			return null;
		}

		$category = $json['recipeCategory'];
		if (is_array($category)) {
			if (count($category) > 0) {
				$category = $category[0];
			} else {
				$category = null;
			}
		}

		if (strlen(trim($category)) == 0) {
			return null;
		}
		return $category;
	}

	private function updateKeywords() {
		$newPairs = [];
		$obsoletePairs = [];

		foreach ($this->jsonFiles as $rid => $json) {
			$textKeywords = $json['keywords'] ?? '';
			if (is_array($textKeywords)) {
				$keywords = $textKeywords;
			} else {
				$keywords = explode(',', $textKeywords);
			}
			$keywords = array_map(function ($v) {
				return trim($v);
			}, $keywords);
			$keywords = array_filter($keywords, function ($v) {
				return ! empty($v);
			});

			$dbKeywords = $this->dbKeywords[$rid];

			$onlyInDb = array_filter($dbKeywords, function ($v) use ($keywords) {
				return empty(array_keys($keywords, $v));
			});
			$onlyInJSON = array_filter($keywords, function ($v) use ($dbKeywords) {
				return empty(array_keys($dbKeywords, $v));
			});

			$newPairs = array_merge($newPairs, array_map(function ($keyword) use ($rid) {
				return [
					'recipeId' => $rid,
					'name' => $keyword
				];
			}, $onlyInJSON));
			$obsoletePairs = array_merge($obsoletePairs, array_map(function ($keyword) use ($rid) {
				return [
					'recipeId' => $rid,
					'name' => $keyword
				];
			}, $onlyInDb));
		}

		$this->db->addKeywordPairs($newPairs, $this->userId);
		$this->db->removeKeywordPairs($obsoletePairs, $this->userId);
	}

	/**
	 * Gets the last time the search index was updated
	 */
	public function getSearchIndexLastUpdateTime() {
		return $this->userConfigHelper->getLastIndexUpdate();
	}

	/**
	 * @return int
	 */
	public function getSearchIndexUpdateInterval(): int {
		$interval = $this->userConfigHelper->getUpdateInterval();

		if ($interval < 1) {
			$interval = 5;
		}

		return $interval;
	}

	public function triggerCheck() {
		// TODO Locking
		// XXX Catch Exceptions
		$this->checkSearchIndexUpdate();
	}

	/**
	 * Checks if a search index update is needed and performs it
	 */
	private function checkSearchIndexUpdate() {
		$last_index_update = $this->getSearchIndexLastUpdateTime();
		$interval = $this->getSearchIndexUpdateInterval();

		if ($last_index_update < 1 || time() > $last_index_update + ($interval * 60)) {
			$this->updateCache();

			// Cache the last index update
			$this->userConfigHelper->setLastIndexUpdate(time());

			// TODO Make triggers more general, need refactoring of *all* Services
			$this->recipeService->updateSearchIndex();
		}
	}
}
