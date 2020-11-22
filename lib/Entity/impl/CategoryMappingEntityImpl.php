<?php

namespace OCA\Cookbook\Entity\impl;

use OCA\Cookbook\Db\CategoryMappingDbWrapper;

class CategoryMappingEntityImpl extends AbstractEntity {
	// XXX Add interface?
	
	/**
	 * @var CategoryMappingDbWrapper
	 */
	private $wrapper;
	
	/**
	 * @var RecipeEntityImpl
	 */
	private $recipe;
	
	/**
	 * @var CategoryEntityImpl
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
		$this->setPersisted();
	}
	
	/**
	 * @return \OCA\Cookbook\Entity\RecipeEntity
	 */
	public function getRecipe()
	{
		return $this->recipe;
	}
	
	/**
     * @return \OCA\Cookbook\Entity\CategoryEntity
     */
    public function getCategory()
    {
        return $this->category;
    }

	/**
     * @param \OCA\Cookbook\Entity\RecipeEntity $recipe
     */
    public function setRecipe($recipe)
    {
        $this->recipe = $recipe;
    }

	/**
     * @param \OCA\Cookbook\Entity\CategoryEntity $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
    
	public function clone(): CategoryMappingEntityImpl
    {
		$ret = $this->wrapper->createEntity();
		
		$ret->setCategory($this->category);
		$ret->setRecipe($this->recipe);
		
		if($this->isPersisted())
		{
			$ret->setPersisted();
		}
		
		return $ret;
	}

	public function remove(): void
    {
		$this->wrapper->remove($this);
	}
	
	protected function equalsImpl(AbstractEntity $other): bool
    {
		return $this->category->isSame($other->category) && $this->recipe->isSame($other->recipe);
	}

	protected function isSameImpl(AbstractEntity $other): bool
    {
		return $this->equalsImpl($other);
	}

	


}
