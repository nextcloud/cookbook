<?php

namespace OCA\Cookbook\Entity;

interface KeywordEntity extends Entity {
	/**
	 * @return string The name of the keyword
	 */
	public function getName(): string;
	/**
	 * @param string $name The new name of the keyword
	 */
	public function setName(string $name): void;
	
	/**
	 * @return RecipeEntity[] The recipes associated with the keyword
	 */
	public function getRecipes(): array;
}
