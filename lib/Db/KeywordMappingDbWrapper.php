<?php

namespace OCA\Cookbook\Db;

use OCP\IDBConnection;
use OCA\Cookbook\Entity\KeywordMappingEntity;

class KeywordMappingDbWrapper extends AbstractDbWrapper {
	
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
// 		$qb = $this->db->getQueryBuilder();
		
// 		$qb ->select('name')
// 			->from('cookbook_categories')
// 			->where('user_id = :uid')
// 			->groupBy('name')
// 			->orderBy('name');
		
// 		$qb->setParameter('uid', $this->userId);
		
// 		$res = $qb->execute();
// 		$ret = [];
// 		while ($row = $res->fetch()) {
// 			$entity = new CategoryEntity($this);
// 			$entity->setName($row['name']);
			
// 			$ret[] = $entity;
// 		}
		
// 		$res->closeCursor();
		
// 		return $ret;
	}
	
	/**
	 * Store a single entity back to the database
	 * @param KeywordMappingEntity $category The entity to store
	 */
	public function store(KeywordMappingEntity $mapping): void {
	}
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return KeywordMappingEntity The new entity
	 */
	public function createEntity(): KeywordMappingEntity {
		return new KeywordMappingEntity($this);
	}
}
