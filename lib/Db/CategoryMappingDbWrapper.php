<?php

namespace OCA\Cookbook\Db;

use OCA\Cookbook\Entity\impl\CategoryMappingEntityImpl;
use OCA\Cookbook\Exception\InvalidDbStateException;
use OCP\IDBConnection;
use OCP\IL10N;

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
	
	/**
	 * @var IL10N
	 */
	private $l;
	
	public function __construct(string $UserId, IDBConnection $db, IL10N $l) {
		parent::__construct($db);
		$this->userId = $UserId;
		$this->db = $db;
		$this->l = $l;
	}
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return CategoryMappingEntityImpl The new entity
	 */
	public function createEntity(): CategoryMappingEntityImpl {
		return new CategoryMappingEntityImpl($this);
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
			$recipe = $this->getWrapperServiceLocator()->getRecipeDbWrapper()->getRecipeById($row['recipe_id']);
			
			$category = $this->getWrapperServiceLocator()->getCategoryDbWrapper()->createEntity();
			$category->setName($row['name']);
			
			$entity = $this->createEntity();
			
			$entity->setCategory($category);
			$entity->setRecipe($recipe);
			
			$ret[] = $entity;
		}
		
		$res->closeCursor();
		
		return $ret;
	}
	
	/**
	 * Store a single entity back to the database
	 * @param CategoryMappingEntityImpl $category The entity to store
	 */
	public function store(CategoryMappingEntityImpl $mapping): void {
		if(! $mapping->getRecipe()->isPersisted())
		{
			throw new InvalidDbStateException($this->l->t('The recipe was not stored to the database yet. No id known.'));
		}
		
		if($mapping->isPersisted()){
			$this->update($mapping);
		}
		else {
			$this->storeNew($mapping);
		}
	}
	
	private function storeNew(CategoryMappingEntityImpl $mapping): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->insert(self::CATEGORIES)
			->values([
				'recipe_id' => $mapping->getRecipe()->getId(),
				'user_id' => $this->userId,
				'name' => $mapping->getCategory()->getName()
			]);
		$qb->execute();
		
		$cache[] = $mapping->clone();
		$this->setEntites($cache);
	}
	
	private function update(CategoryMappingEntityImpl $mapping): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->update(self::CATEGORIES)
			->set('name', $mapping->getCategory()->getName())
			->where('recipe_id = :rid', 'user_id = :uid');
		$qb->setParameters([
			'rid' => $mapping->getRecipe()->getId(),
			'uid' => $this->userId
			]);
		$qb->execute();
		
		$cache = array_map(function (CategoryMappingEntityImpl $m) use ($mapping) {
			if($m->isSame($mapping))
			{
				// We need to update
				return $mapping->clone();
			}
			else {
				return $m;
			}
		}, $cache);
		$this->setEntites($cache);
	}
	
	public function remove(CategoryMappingEntityImpl $mapper): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->delete(self::CATEGORIES)
			->where('recipe_id = :rid', 'user_id = :uid');
		$qb->setParameters([
			'rid' => $mapper->getRecipe()->getId(),
			'uid' => $this->userId
		]);
		
		$qb->execute();
		
		$cache = array_filter($cache, function (CategoryMappingEntityImpl $m) use ($mapper) {
			return ! $m->isSame($mapper);
		});
		$this->setEntites($cache);
		
		// XXX Remove CategoryEntity completely?
	}
}
