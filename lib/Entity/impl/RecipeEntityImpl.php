<?php

namespace OCA\Cookbook\Entity\impl;

use OCA\Cookbook\Db\RecipeDbWrapper;
use OCA\Cookbook\Entity\CategoryEntity;
use OCA\Cookbook\Entity\KeywordEntity;
use OCA\Cookbook\Entity\RecipeEntity;
use OCA\Cookbook\Exception\EntityNotFoundException;
use OCP\IL10N;

class RecipeEntityImpl extends AbstractEntity implements RecipeEntity {
	
	/**
	 * @var RecipeDbWrapper
	 */
	private $wrapper;
	
	/**
	 * @var IL10N
	 */
	private $l;
	
	/**
	 * @var int
	 */
	private $id;
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var CategoryEntityImpl
	 */
	private $newCategory;
	
	/**
	 * @var bool
	 */
	private $setNewCategory;
	
	/**
	 * @var KeywordEntity[]
	 */
	private $newKeywords;
	
	/**
	 * @var KeywordEntity[]
	 */
	private $removedKeywords;
	
	/**
	 * Creat a new entity object
	 * Do not use this constructor directly but create new entities from the corresponding wrapper.
	 * @param RecipeDbWrapper $wrapper The wrapper to use for DB access
	 */
	public function __construct(RecipeDbWrapper $wrapper, IL10N $l) {
		$this->wrapper = $wrapper;
		
		$this->newCategory = null;
		$this->setNewCategory = false;
		$this->newKeywords = [];
		$this->removedKeywords = [];
	}
	
	public function persist(): void {
		$this->wrapper->store($this);
		$this->setPersisted();
		
		$this->newCategory = null;
		$this->newKeywords = [];
		$this->removedKeywords = [];
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
	 * @return CategoryEntityImpl
	 */
	public function getCategory() {
		return $this->setNewCategory ? $this->newCategory : $this->wrapper->getCategory($this);
	}

	/**
	 * @param CategoryEntity $category
	 */
	public function setCategory(CategoryEntity $category) {
		$this->newCategory = $category;
		$this->setNewCategory = true;
	}

	public function remove(): void {
		$this->wrapper->remove($this);
	}
	
	protected function equalsImpl(AbstractEntity $other): bool {
		if (! $this->isSame($other)) {
			return false;
		}
		
		// Compare internal structures
		if ($this->name !== $other->name) {
			return false;
		}
		
		// FIXME Compare references as well?
	}

	public function getKeywords(): array {
		$keywords = $this->wrapper->getKeywords($this);
		
		// Filter out all removed keywords
		foreach ($this->removedKeywords as $rkw) {
			$keywords = $this->filterOutKeyword($keywords, $rkw);
		}
		
		// Add newly added keywords
		$keywords = array_merge($keywords, $this->newKeywords);
		
		return $keywords;
	}

	public function removeKeyword(KeywordEntity $keyword): void {
		if ($this->isKeywordInList($keyword, $this->removedKeywords)) {
			// The keyword is already mentioned as to be removed.
			return;
		}
		
		if ($this->isKeywordInList($keyword, $this->newKeywords)) {
			// We recently added the keyword. Remove it simply from the adding list
			$this->newKeywords = $this->filterOutKeyword($this->newKeywords, $keyword);
		} else {
			$dbKeywords = $this->wrapper->getKeywords($this);
			
			if ($this->isKeywordInList($keyword, $dbKeywords)) {
				// It is present in the DB, remove it
				$this->removedKeywords[] = $keyword;
			} else {
				// We have the keyword not in the DB.
				throw new EntityNotFoundException($this->l->t('Cannot remove a keyword not been assigned to recipe.'));
			}
		}
	}

	public function reload(): void {
		// FIXME
	}

	public function addKeyword(KeywordEntity $keyword): void {
		if ($this->isKeywordInList($keyword, $this->removedKeywords)) {
			// If we should remove the keyword previously, just drop the removal
			$this->removedKeywords = $this->filterOutKeyword($this->removedKeywords, $keyword);
			return;
		}
		
		$dbKeywords = $this->wrapper->getKeywords($this);
		if ($this->isKeywordInList($keyword, $dbKeywords)) {
			// 			throw new InvalidDbStateException($this->l->t('Cannot add a keyword multiple times.'));
			return;
			// XXX Better silent ignorance or exception
		}
		
		$this->newKeywords[] = $keyword;
	}

	protected function isSameImpl(AbstractEntity $other): bool {
		return $this->id == $other->id;
	}

	public function clone(): RecipeEntity {
		$ret = $this->wrapper->createEntity();
		
		$ret->id = $this->id;
		$ret->name = $this->name;
		$ret->newCategory = $this->newCategory;
		$ret->newKeywords = $this->newKeywords;
		$ret->removedKeywords = $this->removedKeywords;
		
		if ($this->isPersisted()) {
			$ret->setPersisted();
		}
		
		return $ret;
	}
	
	/**
	 * @param KeywordEntityImpl[] $keywords
	 * @param KeywordEntityImpl $list
	 * @return KeywordEntityImpl[]
	 */
	private function filterOutKeyword(array $list, KeywordEntityImpl $keyword): array {
		return array_filter($list, function ($kw) use ($keyword) {
			if ($keyword->equals($kw)) {
				return false;
			}
			return true;
		});
	}
	
	private function isKeywordInList(KeywordEntityImpl $keyword, array $list): bool {
		$oldNumber = count($list);
		$list = $this->filterOutKeyword($list, $keyword);
		$newNumber = count($list);
		
		return $oldNumber != $newNumber;
	}
	
	/**
	 * @return ?CategoryEntityImpl
	 */
	public function getNewCategory(): ?CategoryEntityImpl {
		return $this->newCategory;
	}

	/**
	 * @return KeywordEntityImpl[]
	 */
	public function getNewKeywords(): array {
		return $this->newKeywords;
	}

	/**
	 * @return KeywordEntityImpl[]
	 */
	public function getRemovedKeywords(): array {
		return $this->removedKeywords;
	}
	
	/**
	 * @return boolean
	 */
	public function newCategoryWasSet() {
		return $this->setNewCategory;
	}
}
