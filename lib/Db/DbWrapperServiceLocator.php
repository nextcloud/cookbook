<?php

namespace OCA\Cookbook\Db;

class DbWrapperServiceProvider {
	
	/**
	 * @var RecipeDbWrapper
	 */
	private $recipeDbWrapper;
	/**
	 * @var CategoryDbWrapper
	 */
	private $categoryDbWrapper;
	/**
	 * @var CategoryMappingDbWrapper
	 */
	private $categoryMappingDbWrapper;
	/**
	 * @var KeywordDbWrapper
	 */
	private $keywordDbWrapper;
	/**
	 * @var KeywordMappingDbWrapper
	 */
	private $keywordMappingDbWrapper;
	
	public function __construct(RecipeDbWrapper $recipeWrapper, CategoryDbWrapper $categoryWrapper,
		CategoryMappingDbWrapper $categoryMappingWrapper, KeywordDbWrapper $keywordWrapper,
		KeywordMappingDbWrapper $keywordMappingWrapper) {
		
		// Store the wrappers locally
		$this->recipeDbWrapper = $recipeWrapper;
		$this->categoryDbWrapper = $categoryWrapper;
		$this->categoryMappingDbWrapper = $categoryMappingWrapper;
		$this->keywordDbWrapper = $keywordWrapper;
		$this->keywordMappingDbWrapper = $keywordMappingWrapper;
		
		// Register the service locator with each
		$recipeWrapper->setWrapperServiceLocator($this);
		$categoryWrapper->setWrapperServiceLocator($this);
		$categoryMappingWrapper->setWrapperServiceLocator($this);
		$keywordWrapper->setWrapperServiceLocator($this);
		$keywordMappingWrapper->setWrapperServiceLocator($this);
	}
	/**
	 * @return \OCA\Cookbook\Db\RecipeDbWrapper
	 */
	public function getRecipeDbWrapper() {
		return $this->recipeDbWrapper;
	}

	/**
	 * @return \OCA\Cookbook\Db\CategoryDbWrapper
	 */
	public function getCategoryDbWrapper() {
		return $this->categoryDbWrapper;
	}

	/**
	 * @return \OCA\Cookbook\Db\CategoryMappingDbWrapper
	 */
	public function getCategoryMappingDbWrapper() {
		return $this->categoryMappingDbWrapper;
	}

	/**
	 * @return \OCA\Cookbook\Db\KeywordDbWrapper
	 */
	public function getKeywordDbWrapper() {
		return $this->keywordDbWrapper;
	}

	/**
	 * @return \OCA\Cookbook\Db\KeywordMappingDbWrapper
	 */
	public function getKeywordMappingDbWrapper() {
		return $this->keywordMappingDbWrapper;
	}
}
