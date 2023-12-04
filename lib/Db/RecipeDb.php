<?php

namespace OCA\Cookbook\Db;

use DateTimeImmutable;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\DB\Types;
use OCP\IDBConnection;
use OCP\IL10N;

class RecipeDb {
	private const DB_TABLE_RECIPES = 'cookbook_names';
	private const DB_TABLE_KEYWORDS = 'cookbook_keywords';
	private const DB_TABLE_CATEGORIES = 'cookbook_categories';

	/** @var IDBConnection */
	private $db;

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(
		IDBConnection $db,
		IL10N $l
	) {
		$this->db = $db;
		$this->l = $l;
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 * @deprecated
	 * @todo Why deprecated?
	 */
	public function findRecipeById(int $id) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from(self::DB_TABLE_RECIPES)
			->where('recipe_id = :id');
		$qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

		$cursor = $qb->execute();
		$row = $cursor->fetch();
		$cursor->closeCursor();

		if ($row === false) {
			throw new DoesNotExistException($this->l->t("Recipe with ID %d was not found in database.", [$id]));
		}

		$ret = [];
		$ret['name'] = $row['name'];
		$ret['id'] = $row['recipe_id'];
		$ret['dateCreated'] = $row['date_created'];
		$ret['dateModified'] = $row['date_modified'];

		return $ret;
	}

	public function deleteRecipeById(int $id) {
		$qb = $this->db->getQueryBuilder();

		$qb->delete(self::DB_TABLE_RECIPES)
			->where('recipe_id = :id');
		$qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

		$qb->execute();

		$qb = $this->db->getQueryBuilder();

		$qb->delete(self::DB_TABLE_KEYWORDS)
			->where('recipe_id = :id');
		$qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

		$qb->execute();

		$qb = $this->db->getQueryBuilder();

		$qb->delete(self::DB_TABLE_CATEGORIES)
			->where('recipe_id = :id');
		$qb->setParameter('id', $id, IQueryBuilder::PARAM_INT);

		$qb->execute();
	}

	public function findAllRecipes(string $user_id) {
		$qb = $this->db->getQueryBuilder();

		$qb->select(['r.recipe_id', 'r.name', 'r.date_created', 'r.date_modified', 'k.name AS keywords', 'c.name AS category'])
			->from(self::DB_TABLE_RECIPES, 'r')
			->where('r.user_id = :user')
			->orderBy('r.name')
			->orderBy('k.name');
		$qb->setParameter('user', $user_id, Types::STRING);
		$qb->leftJoin('r', self::DB_TABLE_KEYWORDS, 'k',
			$qb->expr()->andX(
				'r.recipe_id = k.recipe_id',
				'k.user_id = :user'
			)
		);
		$qb->leftJoin('r', self::DB_TABLE_CATEGORIES, 'c',
			$qb->expr()->andX(
				'c.recipe_id = r.recipe_id',
				'c.user_id = r.user_id'
			)
		);

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();
		$cursor->closeCursor();

		$result = $this->mapDbNames($result);

		$result = $this->sortRecipes($result);

		// group recipes, convert keywords to comma-separated list
		$recipesGroupedTags = $this->groupKeywordInResult($result);

		return $this->unique($recipesGroupedTags);
	}

	private function mapDbNames(array $results) {
		return array_map(function ($x) {
			$x['dateCreated'] = $x['date_created'];
			$x['dateModified'] = $x['date_modified'];
			unset($x['date_created']);
			unset($x['date_modified']);

			return $x;
		}, $results);
	}

	public function unique(array $result) {
		// NOTE: This post processing shouldn't be necessary
		// When sharing recipes with other users, they are occasionally returned twice
		// See issue #149 for details
		$unique_result = [];

		foreach ($result as $recipe) {
			if (!isset($recipe['recipe_id'])) {
				continue;
			}
			if (isset($unique_result[$recipe['recipe_id']])) {
				continue;
			}

			$unique_result[$recipe['recipe_id']] = $recipe;
		}

		return array_values($unique_result);
	}

	private function sortRecipes(array $recipes): array {
		usort($recipes, function ($a, $b) {
			return strcasecmp($a['name'], $b['name']);
		});

		return $recipes;
	}

	public function findAllKeywords(string $user_id) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('k.name')
			->selectAlias($qb->createFunction('COUNT(k.recipe_id)'), 'recipe_count')
			->from(self::DB_TABLE_KEYWORDS, 'k')
			->where('user_id = :user AND k.name != \'\'')
			->groupBy('k.name')
			->orderBy('k.name');
		$qb->setParameter('user', $user_id, Types::STRING);

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();
		$cursor->closeCursor();

		$result = array_map(function ($x) {
			$x['recipe_count'] = (int) $x['recipe_count'];
			return $x;
		}, $result);

		$result = $this->sortRecipes($result);

		$result = array_unique($result, SORT_REGULAR);
		$result = array_filter($result);

		return $result;
	}

	public function findAllCategories(string $user_id) {
		$qb = $this->db->getQueryBuilder();

		// Get all named categories
		$qb->select('c.name')
			->selectAlias($qb->createFunction('COUNT(c.recipe_id)'), 'recipe_count')
			->from(self::DB_TABLE_CATEGORIES, 'c')
			->where('user_id = :user')
			->groupBy('c.name')
			->orderBy('c.name');
		$qb->setParameter('user', $user_id, Types::STRING);

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();
		$cursor->closeCursor();

		$result = array_map(function ($x) {
			$x['recipe_count'] = (int) $x['recipe_count'];
			return $x;
		}, $result);

		$qb = $this->db->getQueryBuilder();

		// Get count of recipes without category
		$qb->select($qb->createFunction('COUNT(1) as cnt'))
			->from(self::DB_TABLE_RECIPES, 'r')
			->leftJoin(
				'r',
				self::DB_TABLE_CATEGORIES,
				'c',
				$qb->expr()->andX(
					'r.user_id = c.user_id',
					'r.recipe_id = c.recipe_id'
				)
			)
			->where(
				$qb->expr()->eq('r.user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)),
				$qb->expr()->isNull('c.name')
			);

		$cursor = $qb->execute();
		$row = $cursor->fetch();
		$cursor->closeCursor();

		$result[] = [
			'name' => '*',
			'recipe_count' => (int) $row['cnt']
		];

		$result = array_unique($result, SORT_REGULAR);
		$result = array_filter($result);

		return $result;
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 *
	 * Using '_' as a placeholder for recipes w/o category
	 */
	public function getRecipesByCategory(string $category, string $user_id) {
		$qb = $this->db->getQueryBuilder();

		if ($category !== '_') {
			// One would probably want to use GROUP_CONCAT to create the list of keywords
			// for the recipe, but those don't seem to work:
			// $qb->select(['r.recipe_id', 'r.name', 'GROUP_CONCAT(k.name) AS keywords']) // not working
			// $qb->select(['r.recipe_id', 'r.name', DB::raw('GROUP_CONCAT(k.name) AS keywords')]) // not working
			$qb->select(['r.recipe_id', 'r.name', 'r.date_created', 'r.date_modified', 'k.name AS keywords'])
				->from(self::DB_TABLE_CATEGORIES, 'c')
				->where('c.name = :category')
				->andWhere('c.user_id = :user')
				->setParameter('category', $category, Types::STRING)
				->setParameter('user', $user_id, Types::STRING);

			$qb->join('c', self::DB_TABLE_RECIPES, 'r', 'c.recipe_id = r.recipe_id');
			$qb->leftJoin('c', self::DB_TABLE_KEYWORDS, 'k', 'c.recipe_id = k.recipe_id');

			$qb->groupBy(['r.name', 'r.recipe_id', 'k.name', 'r.date_created','r.date_modified']);
			$qb->orderBy('r.name');
		} else {
			$qb->select(['r.recipe_id', 'r.name', 'r.date_created', 'r.date_modified', 'k.name AS keywords'])
			->from(self::DB_TABLE_RECIPES, 'r')
			->leftJoin('r', self::DB_TABLE_KEYWORDS, 'k', 'r.recipe_id = k.recipe_id')
			->leftJoin(
				'r',
				self::DB_TABLE_CATEGORIES,
				'c',
				$qb->expr()->andX(
					'r.user_id = c.user_id',
					'r.recipe_id = c.recipe_id'
				)
			)
			->where(
				$qb->expr()->eq('r.user_id', $qb->createNamedParameter($user_id, IQueryBuilder::PARAM_STR)),
				$qb->expr()->isNull('c.name')
			);
		}

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();
		$cursor->closeCursor();

		$result = $this->mapDbNames($result);
		$result = array_map(function ($x) use ($category) {
			if ($category === '_') {
				$x['category'] = null;
			} else {
				$x['category'] = $category;
			}
			return $x;
		}, $result);

		// group recipes, convert keywords to comma-separated list
		$recipesGroupedTags = $this->groupKeywordInResult($result);

		return $this->unique($recipesGroupedTags);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 *
	 */
	public function getRecipesByKeywords(string $keywords, string $user_id) {
		$keywords_arr = explode(',', $keywords);

		$qb = $this->db->getQueryBuilder();

		$qb->select(['r.recipe_id', 'r.name', 'r.date_created', 'r.date_modified', 'kk.name AS keywords', 'c.name AS category'])
			->from(self::DB_TABLE_KEYWORDS, 'k')
			->where('k.name IN (:keywords)')
			->andWhere('k.user_id = :user')
			->having('COUNT(DISTINCT k.name) = :keywordsCount')
			->setParameter('user', $user_id, Types::INTEGER)
			->setParameter('keywords', $keywords_arr, IQueryBuilder::PARAM_STR_ARRAY)
			->setParameter('keywordsCount', sizeof($keywords_arr), Types::INTEGER);
		$qb->join('k', self::DB_TABLE_RECIPES, 'r', 'k.recipe_id = r.recipe_id')
			->join('r', self::DB_TABLE_CATEGORIES, 'c', 'c.recipe_id = r.recipe_id');
		$qb->join('r', self::DB_TABLE_KEYWORDS, 'kk', 'kk.recipe_id = r.recipe_id');
		$qb->groupBy(['r.name', 'r.recipe_id', 'kk.name', 'r.date_created', 'r.date_modified', 'c.name']);
		$qb->orderBy('r.name');

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();
		$cursor->closeCursor();

		$result = $this->mapDbNames($result);

		// group recipes, convert keywords to comma-separated list
		$recipesGroupedTags = $this->groupKeywordInResult($result);

		return $this->unique($recipesGroupedTags);
	}

	/**
	 * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
	 */
	public function findRecipes(array $keywords, string $user_id) {
		$has_keywords = $keywords && is_array($keywords) && $keywords[0];

		if (!$has_keywords) {
			return $this->findAllRecipes($user_id);
		}

		$qb = $this->db->getQueryBuilder();

		$qb->select(['r.recipe_id', 'r.name', 'r.date_created', 'r.date_modified', 'kk.name AS keywords', 'c.name AS category'])
			->from(self::DB_TABLE_RECIPES, 'r');

		$qb->leftJoin('r', self::DB_TABLE_KEYWORDS, 'k', 'k.recipe_id = r.recipe_id');
		$qb->leftJoin('r', self::DB_TABLE_CATEGORIES, 'c', 'r.recipe_id = c.recipe_id');
		$qb->leftJoin('r', self::DB_TABLE_KEYWORDS, 'kk', 'kk.recipe_id = r.recipe_id');

		$paramIdx = 1;
		$params = [];
		$types = [];

		foreach ($keywords as $keyword) {
			$lowerKeyword = strtolower($keyword);

			$qb->orWhere("LOWER(k.name) LIKE :keyword$paramIdx");
			$qb->orWhere("LOWER(r.name) LIKE :keyword$paramIdx");
			$qb->orWhere("LOWER(c.name) LIKE :keyword$paramIdx");

			$params["keyword$paramIdx"] = "%$lowerKeyword%";
			$types["keyword$paramIdx"] = Types::STRING;
			$paramIdx++;
		}

		$qb->andWhere('r.user_id = :user');

		$qb->setParameters($params, $types);
		$qb->setParameter('user', $user_id, Types::STRING);

		$qb->groupBy(['r.name', 'r.recipe_id', 'kk.name', 'r.date_created', 'r.date_modified', 'c.name']);
		$qb->orderBy('r.name');

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();
		$cursor->closeCursor();

		$result = $this->mapDbNames($result);

		// group recipes, convert keywords to comma-separated list
		$recipesGroupedTags = $this->groupKeywordInResult($result);

		return $this->unique($recipesGroupedTags);
	}

	/**
	 * @param array $results Array of recipes with double entries for different keywords
	 * Group recipes by id and convert keywords to comma-separated list
	 */
	public function groupKeywordInResult(array $result) {
		$recipesGroupedTags = [];
		foreach ($result as $recipe) {
			if (!array_key_exists($recipe['recipe_id'], $recipesGroupedTags)) {
				$recipesGroupedTags[$recipe['recipe_id']] = $recipe;
			} else {
				if (!is_null($recipe['keywords'])) {
					$recipesGroupedTags[$recipe['recipe_id']]['keywords'] .= ','.$recipe['keywords'];
				}
			}
		}
		return $recipesGroupedTags;
	}

	/**
	 * @param string $user_id
	 * @deprecated
	 */
	public function emptySearchIndex(string $user_id) {
		$qb = $this->db->getQueryBuilder();
		$qb->delete(self::DB_TABLE_RECIPES)
			->where('user_id = :user')
			->orWhere('user_id = :empty');
		$qb->setParameter('user', $user_id, Types::STRING);
		$qb->setParameter('empty', 'empty', Types::STRING);
		$qb->execute();

		$qb = $this->db->getQueryBuilder();
		$qb->delete(self::DB_TABLE_KEYWORDS)
			->where('user_id = :user')
			->orWhere('user_id = :empty');
		$qb->setParameter('user', $user_id, Types::STRING);
		$qb->setParameter('empty', 'empty', Types::STRING);
		$qb->execute();

		$qb = $this->db->getQueryBuilder();
		$qb->delete(self::DB_TABLE_CATEGORIES)
			->where('user_id = :user')
			->orWhere('user_id = :empty');
		$qb->setParameter('user', $user_id, Types::STRING);
		$qb->setParameter('empty', 'empty', Types::STRING);
		$qb->execute();
	}

	private function isRecipeEmpty($json) {
	}

	/**
	 * @param array $ids
	 */
	public function deleteRecipes(array $ids, string $userId) {
		if (empty($ids)) {
			return;
		}

		foreach ($ids as $id) {
			// Remove category
			$this->removeCategoryOfRecipe($id, $userId);

			// Remove all keywords
			$keywords = $this->getKeywordsOfRecipe($id, $userId);
			$pairs = array_map(function ($kw) use ($id) {
				return ['recipeId' => $id, 'name' => $kw];
			}, $keywords);
			$this->removeKeywordPairs($pairs, $userId);
		}

		$qb = $this->db->getQueryBuilder();

		$qb->delete(self::DB_TABLE_RECIPES);

		foreach ($ids as $id) {
			$qb->orWhere(
				$qb->expr()->andX(
					$qb->expr()->eq('recipe_id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)),
					$qb->expr()->eq("user_id", $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
				));
		}

		$qb->execute();
	}

	/**
	 * @param array $recipes
	 */
	public function insertRecipes(array $recipes, string $userId) {
		if (empty($recipes)) {
			return;
		}

		$qb = $this->db->getQueryBuilder();

		$qb->insert(self::DB_TABLE_RECIPES)
			->values([
				'recipe_id' => ':id',
				'user_id' => ':userid',
				'name' => ':name',
				'date_created' => ':dateCreated',
				'date_modified' => ':dateModified',
			]);

		$qb->setParameter('userid', $userId);

		foreach ($recipes as $recipe) {
			$qb->setParameter('id', $recipe['id'], Types::INTEGER);
			$qb->setParameter('name', $recipe['name'], Types::STRING);

			$dateCreated = $this->parseDate($recipe['dateCreated']);
			$qb->setParameter('dateCreated', $dateCreated, Types::DATETIME);

			$dateModified = $this->parseDate($recipe['dateModified']);
			$qb->setParameter('dateModified', $dateModified, Types::DATETIME);

			$qb->execute();
		}
	}

	public function updateRecipes(array $recipes, string $userId) {
		if (empty($recipes)) {
			return;
		}

		foreach ($recipes as $recipe) {
			$qb = $this->db->getQueryBuilder();
			$qb->update(self::DB_TABLE_RECIPES)
				->where('recipe_id = :id', 'user_id = :uid');

			$qb->set('name', $qb->createNamedParameter($recipe['name'], IQueryBuilder::PARAM_STR));

			$dateCreated = $this->parseDate($recipe['dateCreated']);
			$dateModified = $this->parseDate($recipe['dateModified']);

			$qb->set('date_created', $qb->createNamedParameter($dateCreated, IQueryBuilder::PARAM_DATE));
			$qb->set('date_modified', $qb->createNamedParameter($dateModified, IQueryBuilder::PARAM_DATE));

			$qb->setParameter('id', $recipe['id']);
			$qb->setParameter('uid', $userId);

			try {
				$qb->execute();
			} catch (\Exception $ex) {
				throw $ex;
			}
		}
	}

	public function getKeywordsOfRecipe(int $recipeId, string $userId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('name')
			->from(self::DB_TABLE_KEYWORDS)
			->where('recipe_id = :rid', 'user_id = :uid');

		$qb->setParameter('rid', $recipeId);
		$qb->setParameter('uid', $userId);

		$cursor = $qb->execute();
		$result = $cursor->fetchAll();
		$cursor->closeCursor();

		$ret = array_map(function ($row) {
			$r = $row['name'];
			return $r;
		}, $result);

		return $ret;
	}

	public function getCategoryOfRecipe(int $recipeId, string $userId) {
		$qb = $this->db->getQueryBuilder();

		$qb->select('name')
		->from(self::DB_TABLE_CATEGORIES)
		->where('recipe_id = :rid', 'user_id = :uid');

		$qb->setParameter('rid', $recipeId);
		$qb->setParameter('uid', $userId);

		$cursor = $qb->execute();
		$result = $cursor->fetch();
		$cursor->closeCursor();

		if ($result === false) {
			return null;
		} else {
			return $result['name'];
		}
	}

	public function updateCategoryOfRecipe(int $recipeId, string $categoryName, string $userId) {
		$qb = $this->db->getQueryBuilder();
		$qb->update(self::DB_TABLE_CATEGORIES)
			->where('recipe_id = :rid', 'user_id = :user');
		$qb->set('name', $qb->createNamedParameter($categoryName, IQueryBuilder::PARAM_STR));
		$qb->setParameter('rid', $recipeId, Types::INTEGER);
		$qb->setParameter('user', $userId, Types::STRING);
		$qb->execute();
	}

	public function addCategoryOfRecipe(int $recipeId, string $categoryName, string $userId) {
		// NOTE: We're using * as a placeholder for no category
		if (empty($categoryName)) {
			$categoryName = '*';
		}
		//         else if(is_array($json['recipeCategory']))
		//         {
		//             $json['recipeCategory'] = reset($json['recipeCategory']);
		//         }

		$qb = $this->db->getQueryBuilder();
		$qb->insert(self::DB_TABLE_CATEGORIES)
			->values(['recipe_id' => ':rid', 'name' => ':name', 'user_id' => ':user']);
		$qb->setParameter('rid', $recipeId, Types::INTEGER);
		$qb->setParameter('name', $categoryName, Types::STRING);
		$qb->setParameter('user', $userId, Types::STRING);

		try {
			$qb->execute();
		} catch (\Exception $e) {
			// Category didn't meet restrictions, skip it
		}
	}

	public function removeCategoryOfRecipe(int $recipeId, string $userId) {
		$qb = $this->db->getQueryBuilder();
		$qb->delete(self::DB_TABLE_CATEGORIES)
			->where('recipe_id = :rid', 'user_id = :user');
		$qb->setParameter('rid', $recipeId, Types::INTEGER);
		$qb->setParameter('user', $userId, Types::STRING);
		$qb->execute();
	}

	public function addKeywordPairs(array $pairs, string $userId) {
		if (empty($pairs)) {
			return;
		}

		$qb = $this->db->getQueryBuilder();
		$qb->insert(self::DB_TABLE_KEYWORDS)
			->values(['recipe_id' => ':rid', 'name' => ':name', 'user_id' => ':user']);
		$qb->setParameter('user', $userId, Types::STRING);

		foreach ($pairs as $p) {
			$qb->setParameter('rid', $p['recipeId'], Types::INTEGER);
			$qb->setParameter('name', $p['name'], Types::STRING);

			try {
				$qb->execute();
			} catch (\Exception $ex) {
				// The insertion of a keywaord might conflict with the requirements. Skip it.
			}
		}
	}

	public function removeKeywordPairs(array $pairs, string $userId) {
		if (empty($pairs)) {
			return;
		}

		$qb = $this->db->getQueryBuilder();
		$qb->delete(self::DB_TABLE_KEYWORDS);

		foreach ($pairs as $p) {
			$qb->orWhere(
				$qb->expr()->andX(
					$qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)),
					$qb->expr()->eq('recipe_id', $qb->createNamedParameter($p['recipeId'], IQueryBuilder::PARAM_INT)),
					$qb->expr()->eq('name', $qb->createNamedParameter($p['name'], IQueryBuilder::PARAM_STR))
				)
			);
		}

		$qb->execute();
	}

	private function parseDate(?string $date) {
		if (is_null($date)) {
			return null;
		}

		try {
			return new DateTimeImmutable($date);
		} catch (\Exception $ex) {
			return new DateTimeImmutable();
		}
	}
}
