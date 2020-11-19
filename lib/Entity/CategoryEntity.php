<?php

namespace  OCA\Cookbook\Entity;

use OCAA\Cookbook\Db\CategoryDbWrapper;

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
	}
}
