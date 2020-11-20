<?php

namespace OCA\Cookbook\Entity;

use OCA\Cookbook\Db\RecipeDbWrapper;

class RecipeEntity implements Entity {
	
	/**
	 * @var RecipeDbWrapper
	 */
	private $wrapper;
	
	/**
	 * @var int
	 */
	private $id;
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * Creat a new entity object
	 * Do not use this constructor directly but create new entities from the corresponding wrapper.
	 * @param RecipeDbWrapper $wrapper The wrapper to use for DB access
	 */
	public function __construct(RecipeDbWrapper $wrapper){
		$this->wrapper = $wrapper;
	}
	
	public function persist(): void {
		$this->wrapper->store($this);
	}
	
	/**
	 * Obtain the id of the recipe in the database.
	 * @return number The id of the recipe in the database
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * Get the name of the recipe
	 * @return string The name of the recipe
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set the id of the recipe in the database.
	 * Caution: This should be done only by the corresponding wrapper after inserting into the DB
	 * @param number $id The new id
	 */
	public function setId($id): void {
		$this->id = $id;
	}

	/**
	 * Set the name of the recipe
	 * @param string $name The new name of the recipe
	 */
	public function setName($name): void {
		$this->name = $name;
	}

	public function remove(): void
    {
		$this->wrapper->remove($this);
	}


}
