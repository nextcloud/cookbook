<?php

namespace OCA\Cookbook\Entity;

interface CategoryEntity extends Entity {
	/**
	 * @return string The name of the category
	 */
	public function getName(): string;
	/**
	 * @param string $name The new name of the category
	 */
	public function setName(string $name): void;
	
	/**
	 * @return RecipeEntity[] The recipes associated with the category
	 */
	public function getRecipes(): array;
}
