<?php

namespace  OCA\Cookbook\Db;

use OCA\Cookbook\Entity\RecipeEntity;
use OCP\IDBConnection;
use OCA\Cookbook\Exception\EntityNotFoundException;
use OCP\IL10N;
use OCA\Cookbook\Exception\InvalidDbStateException;
use OCA\Cookbook\Entity\impl\CategoryEntityImpl;
use OCA\Cookbook\Entity\impl\KeywordEntityImpl;
use OCA\Cookbook\Entity\impl\RecipeEntityImpl;
use OCA\Cookbook\Entity\impl\CategoryMappingEntityImpl;
use OCA\Cookbook\Entity\impl\KeywordMappingEntityImpl;

class RecipeDbWrapper extends AbstractDbWrapper {
	private const NAMES = 'cookbook_names';
	
	/**
	 * @var IDBConnection
	 */
	private $db;
	
	/**
	 * @var string
	 */
	private $userId;
	
	/**
	 * @var IL10N
	 */
	private $l;
	
	public function __construct(string $UserId, IDBConnection $db, IL10N $l) {
		parent::__construct($db);
		$this->db = $db;
		$this->userId = $UserId;
		$this->l = $l;
	}
	
	protected function fetchDatabase(): array {
		$qb = $this->db->getQueryBuilder();
		
		$qb ->select('id', 'name')
			->from('cookbook_names')
			->where('user_id = :uid');
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		$ret = [];
		
		while ($row = $res->fetch()) {
			$recipe = $this->createEntity();
			
			$recipe->setName($row['name']);
			$recipe->setId($row['id']);
			
			$ret[] = $recipe;
		}
		
		return $ret;
	}
	
	public function createEntity(): RecipeEntityImpl {
		$ret = new RecipeEntityImpl($this);
		$ret->setId(-1);
		return $ret;
	}
	
	public function store(RecipeEntityImpl $recipe): void {
		if ($recipe->isPersisted()) {
			$this->storeNew($recipe);
		} else {
			$this->store($recipe);
		}
	}
	
	private function storeNew(RecipeEntityImpl $recipe): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->insert(self::NAMES)
			->values([
				'name' => $recipe->getName(),
				'user_id' => $this->userId
			]);
			
		$qb->execute();
		$recipe->setId($qb->getLastInsertId());
		
		// Update cache
		$cache[] = $recipe->clone();
		$this->setEntites($cache);
		
		// Set category mapping
		$cat = $recipe->getCategory();
		$cat->persist();
		
		$cm = $this->getWrapperServiceLocator()->getCategoryMappingDbWrapper()->createEntity();
		$cm->setCategory($cat);
		$cm->setRecipe($recipe);
		$cm->persist();
		
		// Set keyword setting
		$keywordMapperWrapper = $this->getWrapperServiceLocator()->getKeywordMappingDbWrapper();
		foreach ($recipe->getKeywords() as $keyword) {
			/**
			 * @var KeywordEntityImpl $keyword
			 */
			$keyword->persist();
			
			$km = $keywordMapperWrapper->createEntity();
			$km->setRecipe($recipe);
			$km->setKeyword($keyword);
			$km->persist();
		}
	}
	
	private function update(RecipeEntityImpl $recipe): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->update(self::NAMES)
			->set('name', ':name')
			->where('recipe_id = :rid');
		$qb->setParameter('rid', $recipe->getId());
		$qb->setParameter('name', $recipe->getName());
		
		$qb->execute();
		
		// Update cache
		$cache = array_map(function (RecipeEntityImpl $r) use ($recipe) {
			if ($r->isSame($recipe)) {
				return $recipe;
			} else {
				return $r;
			}
		}, $cache);
		$this->setEntites($cache);
		
		// Update the category if needed
		$newCategory = $recipe->getNewCategory();
		if ($recipe->newCategoryWasSet() && ! is_null($newCategory)) {
			$oldMappings = $this->getRecipeCategoryMappings($recipe);
			
			$newCategory->persist();
			
			if (count($oldMappings) == 0) {
				// We need to insert a new Mapping
				$mapping = $this->getWrapperServiceLocator()->getCategoryMappingDbWrapper()->createEntity();
				$mapping->setCategory($newCategory);
				$mapping->setRecipe($recipe);
				$mapping->persist();
			} else {
				$mapping = $oldMappings[0];
				$mapping->setCategory($newCategory);
				$mapping->persist();
			}
		}
		
		// Update the keywords
		$kwWrapper = $this->getWrapperServiceLocator()->getKeywordMappingDbWrapper();
		
		foreach ($recipe->getNewKeywords() as $kw) {
			/**
			 * @var KeywordEntityImpl $kw
			 */
			$kw->persist();
			
			$mapping = $kwWrapper->createEntity();
			$mapping->setRecipe($recipe);
			$mapping->setKeyword($kw);
			$mapping->persist();
		}
		
		$removedKeywords = $recipe->getRemovedKeywords();
		$currentKwMappings = $this->getRecipeKeywordMappings($recipe);
		$removingMappings = array_filter($currentKwMappings, function (KeywordMappingEntityImpl $m) use ($removedKeywords) {
			foreach ($removedKeywords as $kw) {
				/**
				 * @var KeywordEntityImpl $kw
				 */
				if ($m->getKeyword()->isSame($kw)) {
					return true;
				}
			}
			return false;
		});
		
		foreach ($removingMappings as $mapping) {
			/**
			 * @var KeywordMappingEntityImpl $mapping
			 */
			$mapping->remove();
		}
	}
	
	public function remove(RecipeEntityImpl $recipe): void {
		if (! $recipe->isPersisted()) {
			throw new InvalidDbStateException($this->l->t('Cannot remove recipe that was not yet saved.'));
		}
		
		$catMappings = $this->getRecipeCategoryMappings($recipe);
		foreach ($catMappings as $catMapping) {
			/**
			 * @var CategoryMappingEntityImpl $catMapping
			 */
			$catMapping->remove();
		}
		
		$keywordMappings = $this->getRecipeKeywordMappings($recipe);
		foreach ($keywordMappings as $keywordMapping) {
			/**
			 * @var KeywordMappingEntityImpl $keywordMapping
			 */
			$keywordMapping->remove();
		}
		
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->delete(self::NAMES)
			->where('recipe_id = :rid');
		$qb->setParameter('rid', $recipe->getId());
		
		$qb->execute();
		
		$cache = array_filter($cache, function (RecipeEntityImpl $r) use ($recipe) {
			return ! $r->isSame($recipe);
		});
		$this->setEntites($cache);
	}
	
	public function getRecipeById(int $id): RecipeEntity {
		$entities = $this->getEntries();
		
		foreach ($entities as $entry) {
			/**
			 * @var RecipeEntity $entry
			 */
			if ($entry->getId() == $id) {
				return $entry;
			}
		}
		
		throw new EntityNotFoundException($this->l->t('Recipe with id %d was not found.', $id));
	}
	
	public function getCategory(RecipeEntity $recipe): ?CategoryEntityImpl {
		$mappings = $this->getRecipeCategoryMappings($recipe);
		
		if (count($mappings) == 0) {
			return null;
		}
		
		return $mappings[0]->getCategory();
	}
	
	private function getRecipeCategoryMappings(RecipeEntityImpl $recipe): array {
		$mappings = $this->getWrapperServiceLocator()->getCategoryMappingDbWrapper()->getEntries();
		$mappings = array_filter($mappings, function (CategoryMappingEntityImpl $c) use ($recipe) {
			return $c->getRecipe()->isSame($recipe);
		});
			
		if (count($mappings) > 1) {
			throw new InvalidDbStateException($this->l->t('Multiple categopries for a single recipe found.'));
		}
		
		return $mappings;
	}
	
	/**
	 * @param RecipeEntity $recipe
	 * @return KeywordEntityImpl[]
	 */
	public function getKeywords(RecipeEntity $recipe): array {
		$mappings = $this->getRecipeKeywordMappings($recipe);
		$keywords = array_map(function (KeywordMappingEntityImpl $m) {
			return $m->getKeyword();
		}, $mappings);
		
		return $keywords;
	}
	
	private function getRecipeKeywordMappings(RecipeEntityImpl $recipe): array {
		$mappings = $this->getWrapperServiceLocator()->getKeywordMappingDbWrapper()->getEntries();
		$mappings = array_filter($mappings, function (KeywordMappingEntityImpl $m) use ($recipe) {
			return $m->getRecipe()->isSame($recipe);
		});
		
		return $mappings;
	}
}
