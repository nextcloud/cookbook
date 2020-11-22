<?php

namespace OCA\Cookbook\Db;

use OCA\Cookbook\Entity\impl\KeywordMappingEntityImpl;
use OCP\IDBConnection;
use OCA\Cookbook\Exception\InvalidDbStateException;

class KeywordMappingDbWrapper extends AbstractDbWrapper {
	
	private const KEYWORDS = 'cookbook_keywords';
	
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
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return KeywordMappingEntityImpl The new entity
	 */
	public function createEntity(): KeywordMappingEntityImpl {
		return new KeywordMappingEntityImpl($this);
	}
	
	protected function fetchDatabase(): array {
		$qb = $this->db->getQueryBuilder();
		
		$qb ->select('name', 'recipe_id')
			->from(self::KEYWORDS)
			->where('user_id = :uid');
		
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		$ret = [];
		
		while ($row = $res->fetch()) {
			$recipe = $this->getWrapperServiceLocator()->getRecipeDbWrapper()->getRecipeById($row['recipe_id']);
			
			$keyword = $this->getWrapperServiceLocator()->getKeywordDbWrapper()->createEntity();
			$keyword->setName($row['name']);
			
			$entity = $this->createEntity();
			
			$entity->setRecipe($recipe);
			$entity->setKeyword($keyword);
			
			$ret[] = $entity;
		}
		
		$res->closeCursor();
		
		return $ret;
	}
	
	/**
	 * Store a single entity back to the database
	 * @param KeywordMappingEntityImpl $category The entity to store
	 */
	public function store(KeywordMappingEntityImpl $mapping): void {
		if(! $mapping->getRecipe()->isPersisted()){
			throw new InvalidDbStateException($this->l->t('The recipe was not stored to the database yet. No id known.'));
		}
		
		if($mapping->isPersisted())
		{
			$this->update($mapping);
		}
		else {
			$this->storeNew($mapping);
		}
	}
	
	private function storeNew(KeywordMappingEntityImpl $mapping): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->insert(self::KEYWORDS)
			->values([
				'recipe_id' => $mapping->getRecipe()->getId(),
				'user_id' => $this->userId,
				'name' => $mapping->getKeyword()->getName()
			]);
		
		$qb->execute();
		
		$cache[] = $mapping;
		$this->setEntites($cache);
	}
	
	private function update(KeywordMappingEntityImpl $mapping): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->update(self::KEYWORDS)
			->set('name', $mapping->getKeyword()->getName())
			->where('recipe_id = :rid', 'user_id = :uid');
		$qb->setParameter('rid', $mapping->getRecipe()->getId());
		$qb->setParameter('uid', $this->user);
		
		$qb->execute();
		
		$cache = array_map(function(KeywordMappingEntityImpl $m) use ($mapping) {
			if($m->isSame($mapping))
			{
				return $mapping;
			}
			else {
				return $m;
			}
		}, $cache);
		$this->setEntites($cache);
	}
	
	public function remove(KeywordMappingEntityImpl $mapping): void {
		$qb = $this->db->getQueryBuilder();
		
		$cache = $this->getEntries();
		
		$qb ->delete(self::KEYWORDS)
			->where('recipe_id = :rid', 'user_id = :uid');
		$qb->setParameter('rid', $mapping->getRecipe()->getId());
		$qb->setParameter('uid', $this->userId);
		
		$qb->execute();
		
		$cache = array_filter($cache, function(KeywordMappingEntityImpl $m) use ($mapping) {
			return ! $m->isSame($mapping);
		});
		$this->setEntites($cache);
	}
	
}
