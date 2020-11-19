<?php

namespace  OCA\Cookbook\Entity;

use OCAA\Cookbook\Db\KeywordMappingDbWrapper;

class KeywordMappingEntity implements Entity {
	
	/**
	 * @var RecipeEntity
	 */
	private $recipe;
	
	/**
	 * @var KeywordEntity
	 */
	private $keyword;
	
	/**
	 * Create a new category entity
	 * Do not use this constructor directly but create new entities from the corresponding wrapper.
	 * @param KeywordMappingDbWrapper $wrapper The wrapper to use for DB access
	 */
	public function __construct(KeywordMappingDbWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	public function persist(): void {
		$this->wrapper->store($this);
	}
}
