<?php

namespace OCA\Cookbook\Entity\impl;

use OCA\Cookbook\Db\RecipeDbWrapper;
use OCA\Cookbook\Entity\KeywordEntity;
use OCA\Cookbook\Entity\RecipeEntity;

class RecipeEntityImpl extends AbstractEntity implements RecipeEntity {
	
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
	
	/**
     * @return \OCA\Cookbook\Entity\CategoryEntity
     */
    public function getCategory()
    {
    	if(is_null($this->categoryMapper)) {
        	return null;
    	}
    	else {
    		return $this->categoryMapper->getCategory();
    	}
    }

	/**
     * @return array
     */
//     public function getKeywords()
//     {
//     	return array_map(function (KeywordMappingEntity $mapping){
//     		return $mapping->getKeyword();
//     	}, $this->keywordMappers);
//     }

	/**
     * @param \OCA\Cookbook\Entity\CategoryEntity $category
     */
    public function setCategory($category)
    {
    	if(is_null($category))
    	{
    		if(! is_null($this->categoryMapper))
    		{
    			$this->categoryMapper->remove(); // XXX Good?
    		}
    	}
    	else {
	    	if(is_null($this->categoryMapper)){
	    		$this->categoryMapper = $this->wrapper->getWrapperServiceLocator()->getCategoryMappingDbWrapper()->createEntity();
	    	}
    		
	    	$this->categoryMapper->setRecipe($this);
	    	$this->categoryMapper->setCategory($category);
	    	
	    	$category->persist();
	    	$this->categoryMapper->persist(); // XXX Good?
    	}
    }

	/**
     * @param array $keywords
     */
//     public function setKeywords($keywords)
//     {
// //         $this->keywords = $keywords;
// 		$oldKeywords = $this->getKeywords();
		
		
//     }
    
	public function clone(): RecipeEntity
    {
		// FIXME
	}

	public function remove(): void
    {
		$this->wrapper->remove($this);
	}
	
	protected function equalsImpl(AbstractEntity $other): bool
	{
		// FIXME
	}

	public function getKeywords(): array
	{
		// FIXME
	}

	public function removeKeyword(KeywordEntity $keyword): void
	{
		// FIXME
	}

	public function reload(): void
	{
		// FIXME
	}

	public function addKeyword(KeywordEntity $keyword): void
	{
		// FIXME
	}

	protected function isSameImpl(AbstractEntity $other): bool
	{
		// FIXME
	}



}
