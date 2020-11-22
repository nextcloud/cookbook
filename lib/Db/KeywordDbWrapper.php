<?php

namespace OCA\Cookbook\Db;

use OCP\IDBConnection;
use OCA\Cookbook\Entity\KeywordEntity;
use OCA\Cookbook\Entity\impl\KeywordEntityImpl;
use OCA\Cookbook\Entity\impl\KeywordMappingEntityImpl;
use OCA\Cookbook\Entity\impl\RecipeEntityImpl;

class KeywordDbWrapper extends AbstractDbWrapper {
	
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
	
	protected function fetchDatabase(): array {
		$qb = $this->db->getQueryBuilder();
		
		$qb ->select('name')
			->from(self::KEYWORDS)
			->where('user_id = :uid')
			->groupBy('name')
			->orderBy('name');
		
		$qb->setParameter('uid', $this->userId);
		
		$res = $qb->execute();
		$arr = [];
		while ($row = $res->fetch()) {
			$entity = $this->createEntity();
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
		// We do not need to store anything here. The keywords are just virtually generated.
	}
	
	/**
	 * Create a new entity and reegister it with this wrapper
	 * @return KeywordEntity The new entity
	 */
	public function createEntity(): KeywordEntityImpl {
		return new KeywordEntityImpl($this);
	}
	
	/**
	 * @param KeywordEntity $keyword
	 * @return RecipeEntityImpl[] The recipes associated with the keyword
	 */
	public function getRecipes(KeywordEntity $keyword): array{
		$mappings = $this->wrapperLocator->getKeywordMappingDbWrapper()->getEntries();
		$mappings = array_filter($mappings, function (KeywordMappingEntityImpl $mapping) use ($keyword) {
			return $mapping->getKeyword()->isSame($keyword);
		});
		return array_map(function (KeywordMappingEntityImpl $mapping) {
			return $mapping->getRecipe();
		}, $mappings);
	}
	
	public function remove(KeywordEntity $keyword): void
	{
		// Remove all foreign links
		$recipes = $keyword->getRecipes();
		
		foreach($recipes as $r){
			/**
			 * @var RecipeEntity $r
			 */
			$r->removeKeyword($keyword);
		}
		
		// We cannot do anything as the categories are purely virtual.
	}
}
