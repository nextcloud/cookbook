<?php

namespace  OCA\Cookbook\Db;

use OCA\Cookbook\Entity\RecipeEntity;
use OCP\IDBConnection;

class RecipeDbWrapper extends AbstractDbWrapper {
	
	/**
	 * @var IDBConnection
	 */
	private $db;
	
	/**
	 * @var string
	 */
	private $userId;
	
	public function __construct(string $UserId, IDBConnection $db) {
		parent::__construct($db);
		$this->db = $db;
		$this->userId = $UserId;
	}
	
	protected function fetchDatabase(): array {
		// FIXME
	}

	public function store(RecipeEntity $recipe): void {
		// FIXME
	}
	
	public function createEntity(): RecipeEntity {
		// FIXME
	}
}
