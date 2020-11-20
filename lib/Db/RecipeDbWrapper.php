<?php

namespace  OCA\Cookbook\Db;

use OCA\Cookbook\Entity\RecipeEntity;
use OCP\IDBConnection;
use OCA\Cookbook\Exception\EntityNotFoundException;
use OCP\IL10N;

class RecipeDbWrapper extends AbstractDbWrapper {
	
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
		// FIXME
		$qb = $this->db->getQueryBuilder();
		
		$qb ->select('id', 'name')
			->from('cookbook_names')
			->where('user_id = :uid');
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		$ret = [];
		
		while($row = $res->fetch())
		{
			$recipe = $this->createEntity(); // XXX Better create directly?
			$recipe->setName($row['name']);
			$recipe->setId($row['id']);
			
			$ret[] = $recipe;
		}
		
		return $ret;
	}
	
	public function store(RecipeEntity $recipe): void {
		// FIXME
	}
	
	public function createEntity(): RecipeEntity {
		return new RecipeEntity($this);
	}
	
	public function remove(RecipeEntity $recipe): void {
		// FIXME
	}
	
	public function getRecipeById(int $id): RecipeEntity {
		$entities = $this->getEntries();
		
		foreach($entities as $entry)
		{
			/**
			 * @var RecipeEntity $entry
			 */
			if($entry->getId() == $id)
				return $entry;
		}
		
		throw new EntityNotFoundException($this->l->t('Recipe with id %d was not found.', $id));
	}
}
