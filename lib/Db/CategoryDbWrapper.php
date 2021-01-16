<?php

namespace OCA\Cookbook\Db;

use OCA\Cookbook\Entity\CategoryEntity;
use OCA\Cookbook\Entity\impl\CategoryEntityImpl;
use OCA\Cookbook\Entity\impl\CategoryMappingEntityImpl;
use OCA\Cookbook\Entity\impl\RecipeEntityImpl;
use OCP\IDBConnection;

class CategoryDbWrapper extends AbstractDbWrapper {
	private const CATEGORIES = 'cookbook_categories';
	
	/**
	 * @var string
	 */
	private $userId;
	
	/**
	 * @var IDBConnection
	 */
	private $db;
	
	public function __construct(string $UserId, IDBConnection $db) {
		parent::__construct($db);
		$this->userId = $UserId;
		$this->db = $db;
	}
	
	protected function fetchDatabase(): array {
		$qb = $this->db->getQueryBuilder();
		
		$qb ->select('name')
			->from(self::CATEGORIES)
			->where('user_id = :uid')
			->groupBy('name')
			->orderBy('name');
		
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		
		$ret = [];
		while ($row = $res->fetch()) {
			$entity = $this->createEntity();
			$entity->setName($row['name']);
			
			$ret[] = $entity;
		}
		
		$res->closeCursor();
		
		return $ret;
	}
	
	/**
	 * Store a single entity back to the database
	 * @param CategoryEntity $category The entity to store
	 */
	public function store(CategoryEntity $category): void {
		// We do not need to store anything here. The categories are just virtually generated.
	}
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return CategoryEntity The new entity
	 */
	public function createEntity(): CategoryEntityImpl {
		return new CategoryEntityImpl($this);
	}
	
	public function remove(CategoryEntity $category) {
		// Remove all foreign links
		$recipes = $category->getRecipes();
		
		foreach ($recipes as $r) {
			/**
			 * @var RecipeEntity $r
			 */
			$r->setCategory(null);
		}
		
		// We cannot do anything as the categories are purely virtual.
	}
	
	/**
	 * @param CategoryEntity $category
	 * @return RecipeEntityImpl[] The recipes associated with the category
	 */
	public function getRecipes(CategoryEntity $category) : array {
		/**
		 * @var CategoryMappingEntityImpl[] $mappings
		 */
		$mappings = $this->wrapperLocator->getCategoryMappingDbWrapper()->getEntries();
		$mappings = array_filter($mappings, function (CategoryMappingEntityImpl $mapping) use ($category) {
			return $mapping->getCategory()->isSame($category);
		});
		return array_map(function (CategoryMappingEntityImpl $mapping) {
			return $mapping->getRecipe();
		}, $mappings);
	}
}
