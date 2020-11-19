<?php

namespace OCA\Cookbook\Db;

use OCP\IDBConnection;
use OCA\Cookbook\Entity\KeywordEntity;

class KeywordDbWrapper extends AbstractDbWrapper {
	
	/**
	 * @var string
	 */
	private $userId;
	
	/**
	 * @var IDBConnection
	 */
	private $db;
	
	public function __construct(string $UserId, IDBConnection $db) {
		$this->userId = $UserId;
		$this->db = $db;
	}
	
	protected function fetchDatabase(): array {
		$qb = $this->db->getQueryBuilder();
		
		$qb ->select('name')
			->from('cookbook_keywords')
			->where('user_id = :uid')
			->groupBy('name')
			->orderBy('name');
		
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		$arr = [];
		while ($row = $res->fetch()) {
			$entity = new KeywordEntity($this);
			$entity->setName($row['name']);
			
			$arr[] = $entity;
		}
		
		$res->closeCursor();
		
		return $arr;
	}
	
	/**
	 * Store a single entity back to the database
	 * @param KeywordEntity $keyword The entity to store
	 */
	public function store(KeywordEntity $keyword): void {
	}
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return KeywordEntity The new entity
	 */
	public function createEntity(): KeywordEntity {
		return new KeywordEntity($this);
	}
}
