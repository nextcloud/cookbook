<?php

namespace OCA\Cookbook\Db;

use OCP\IDBConnection;
use OCA\Cookbook\Entity\CategoryMappingEntity;

class CategoryMappingDbWrapper extends AbstractDbWrapper {
	
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
		// FIXME
		$qb = $this->db->getQueryBuilder();
		
		$qb ->select('name', 'recipe_id')
			->from(self::CATEGORIES)
			->where('user_id = :uid');
		
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		$ret = [];
		
		while ($row = $res->fetch()) {
			$entity = $this->createEntity();
			$entity->setName($row['name']);
			$entity->setRecipe($this->getWrapperServiceLocator()->getRecipeDbWrapper()->getRecipeById($row['recipe_id']));
			
			$ret[] = $entity;
		}
		
		$res->closeCursor();
		
		return $ret;
	}
	
	/**
	 * Store a single entity back to the database
	 * @param CategoryMappingEntity $category The entity to store
	 */
	public function store(CategoryMappingEntity $mapping): void {
		// FIXME
		$foundMapping = array_filter($this->getEntries(), function (CategoryMappingEntity $entity) use ($mapping) {
			return $entity === $mapping;
		});
		
		$qb = $this->db->getQueryBuilder();
		
		if(count($foundMapping) > 0) {
			// We need to update an existing entry
			$qb ->update(self::CATEGORIES)
				->set('name', $mapping->getCategory()->getName())
				->where('recipe_id = :rid', 'user_id = :uid');
			$qb->setParameters([
				'rid' => $mapping->getRecipe()->getId(),
				'uid' => $this->userId
			]);
		}
		else {
			// We need to create a new entry
			$qb ->insert(self::CATEGORIES)
				->values([
					'recipe_id' => $mapping->getRecipe()->getId(),
					'user_id' => $this->userId,
					'name' => $mapping->getCategory()->getName()
				]);
		}
		
		$qb->execute();
		$this->invalidateCache();
		// TODO Changing of an object will change the cache as well
	}
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return CategoryMappingEntity The new entity
	 */
	public function createEntity(): CategoryMappingEntity {
		return new CategoryMappingEntity($this);
	}
	
	public function remove(CategoryMappingEntity $mapper): void {
		$qb = $this->db->getQueryBuilder();
		
		$qb ->delete(self::CATEGORIES)
			->where('recipe_id = :rid', 'user_id = :uid');
		$qb->setParameters([
			'rid' => $mapper->getRecipe()->getId(),
			'uid' => $this->userId
		]);
		
		$qb->execute();
		$this->invalidateCache();
		
// 		$this->setEntites(array_filter($this->getEntries(), function (CategoryMappingEntity $entity) use ($mapper) {
// 			return $entity !== $mapper;
// 		}));
		
		// XXX Remove CategoryEntity completely?
	}
}
