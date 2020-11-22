<?php

namespace OCA\Cookbook\Entity;

interface RecipeEntity extends Entity {
	/**
	 * @return int The id of the recipe
	 */
	public function getId(): int;

	/**
	 * @return string The name of the recipe
	 */
	public function getName(): string;
	/**
	 * @param string $name The new name of the recipe
	 */
	public function setName(string $name): void;
	
	/**
	 * @return CategoryEntity The current category
	 */
	public function getCategory(): CategoryEntity;
	/**
	 * @param CategoryEntity $category The new category
	 */
	public function setCategory(CategoryEntity $category): void;
	
	/**
	 * @return KeywordEntity[] The current keywords
	 */
	public function getKeywords(): array;
	/**
	 * @param KeywordEntity $keyword The new keyword to add
	 */
	public function addKeyword(KeywordEntity $keyword): void;
	/**
	 * @param KeywordEntity $keyword The keyword to remove
	 */
	public function removeKeyword(KeywordEntity $keyword): void;
}
