<?php

namespace  OCA\Cookbook\Db;

use OCA\Cookbook\Entity\RecipeEntity;
use OCP\IDBConnection;
use OCA\Cookbook\Exception\EntityNotFoundException;
use OCP\IL10N;
use OCA\Cookbook\Exception\InvalidDbStateException;

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
		
		$qb = $this->db->getQueryBuilder();
		
		if($recipe->getId() == -1)
		{
			$qb ->insert(self::NAMES)
				->values([
					'name' => $recipe->getName(),
					'user_id' => $this->userId
				]);
		}
		else {
			$qb ->update(self::NAMES)
				->set('name', ':name')
				->where('recipe_id = :rid');
			$qb->setParameter('rid', $recipe->getId());
			$qb->setParameter('name', $recipe->getName());
		}
		
		$qb->execute();
		$this->invalidateCache();
		
		if($recipe->getId() == -1){
			$recipe->setId($qb->getLastInsertId());
		}
	}
	
	public function createEntity(): RecipeEntity {
		$ret = new RecipeEntity($this);
		$ret->setId(-1);
		return $ret;
	}
	
	public function remove(RecipeEntity $recipe): void {
		// FIXME
		if($recipe->getId() == -1)
		{
			throw new InvalidDbStateException($this->l->t('Cannot remove recipe that was not yet saved.'));
		}
		
		$qb = $this->db->getQueryBuilder();
		
		$qb ->delete(self::NAMES)
			->where('recipe_id = :rid');
		$qb->setParameter('rid', $recipe->getId());
		
		$qb->execute();
		$this->invalidateCache();
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
