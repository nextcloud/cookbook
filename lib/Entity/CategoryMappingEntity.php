<?php

namespace  OCA\Cookbook\Entity;

use OCA\Cookbook\Db\CategoryMappingDbWrapper;


class CategoryMappingEntity implements Entity {
	
	/**
	 * @var RecipeEntity
	 */
	private $recipe;
	
	/**
	 * @var CategoryEntity
	 */
	private $category;
	
	/**
	 * Create a new category entity
	 * Do not use this constructor directly but create new entities from the corresponding wrapper.
	 * @param CategoryMappingDbWrapper $wrapper The wrapper to use for DB access
	 */
	public function __construct(CategoryMappingDbWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	public function persist(): void {
		$this->wrapper->store($this);
	}
}
