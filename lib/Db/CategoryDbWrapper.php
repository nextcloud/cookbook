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
	
	public function __construct(string $UserId, IDBConnection $db) {
		parent::__construct($db);
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
		
		return $ret;
	}
}
