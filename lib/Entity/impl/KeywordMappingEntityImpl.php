<?php

namespace  OCA\Cookbook\Entity\impl;

use OCA\Cookbook\Db\KeywordMappingDbWrapper;

class KeywordMappingEntityImpl extends AbstractEntity {
	// XXX Add interface?
	
	/**
	 * @var RecipeEntityImpl
	 */
	private $recipe;
	
	/**
	 * @var KeywordEntityImpl
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
		$this->setPersisted();
	}
	
	/**
	 * @return RecipeEntityImpl
	 */
	public function getRecipe(): RecipeEntityImpl {
		return $this->recipe;
	}

	/**
	 * @return KeywordEntityImpl
	 */
	public function getKeyword(): KeywordEntityImpl {
		return $this->keyword;
	}

	/**
	 * @param RecipeEntityImpl $recipe
	 */
	public function setRecipe(RecipeEntityImpl $recipe) {
		$this->recipe = $recipe;
	}

	/**
	 * @param KeywordEntityImpl $keyword
	 */
	public function setKeyword(KeywordEntityImpl $keyword) {
		$this->keyword = $keyword;
	}
	
	public function clone(): KeywordMappingEntityImpl {
		$ret = $this->wrapper->createEntity();
		
		$ret->setKeyword($this->keyword);
		$ret->setRecipe($this->recipe);
		
		if ($this->isPersisted()) {
			$ret->setPersisted();
		}
		
		return $ret;
	}

	protected function equalsImpl(AbstractEntity $other): bool {
		return $this->keyword->isSame($other->keyword) && $this->recipe->isSame($other->recipe);
	}

	protected function isSameImpl(AbstractEntity $other): bool {
		return $this->equalsImpl($other);
	}
	
	public function remove(): void {
		$this->wrapper->remove($this);
	}
}
