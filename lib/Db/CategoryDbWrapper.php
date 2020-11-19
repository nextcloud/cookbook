<?php

namespace  OCAA\Cookbook\Db;

use OCA\Cookbook\Db\AbstractDbWrapper;
use OCP\IDBConnection;
use OCA\Cookbook\Entity\CategoryEntity;

class CategoryDbWrapper extends AbstractDbWrapper {
	
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
			->from('cookbook_categories')
			->where('user_id = :uid')
			->groupBy('name')
			->orderBy('name');
		
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		$ret = [];
		while ($row = $res->fetch()) {
			$entity = new CategoryEntity($this);
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
	}
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return CategoryEntity The new entity
	 */
	public function createEntity(): CategoryEntity {
		return new CategoryEntity($this);
	}
}
