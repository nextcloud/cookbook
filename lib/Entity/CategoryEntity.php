<?php

namespace  OCA\Cookbook\Entity;

use OCA\Cookbook\Db\CategoryDbWrapper;

class CategoryEntity implements Entity {
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var CategoryDbWrapper
	 */
	private $wrapper;
	
	/**
	 * Create a new category entity
	 * Do not use this constructor directly but create new entities from the corresponding wrapper.
	 * @param CategoryDbWrapper $wrapper The wrapper to use for DB access
	 */
	public function __construct(CategoryDbWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	/**
	 * Get the name of the category
	 * @return string The name of the category
	 */
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * Set the name of the category.
	 * @param string $name The new name of the category
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}
	
	public function persist(): void {
		$this->wrapper->store($this);
	}

	public function remove(): void
    {
		$this->wrapper->remove($this);
	}

}
