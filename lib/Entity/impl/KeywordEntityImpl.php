<?php

namespace OCA\Cookbook\Entity\impl;

use OCA\Cookbook\Db\KeywordDbWrapper;
use OCA\Cookbook\Entity\KeywordEntity;

class KeywordEntityImpl extends AbstractEntity implements KeywordEntity {
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var KeywordDbWrapper
	 */
	private $wrapper;
	
	/**
	 * Create a new entity.
	 * Do not use this constructor directly but create new entities from the corresponding wrapper.
	 * @param KeywordDbWrapper $wrapper The wrapper to use for DB communication.
	 */
	public function __construct(KeywordDbWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}
	
	/**
	 * Get the name of the keyword
	 * @return string The name of the keyword
	 */
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * Set the name of the keyword
	 * @param string $name The name of the keyword
	 */
	public function setName($name): void {
		$this->name = $name;
	}

	public function persist(): void {
		$this->wrapper->store($this);
		$this->setPersisted();
	}
	
	public function reload(): void {
		// FIXME
	}

	public function clone(): KeywordEntityImpl {
		$ret = $this->wrapper->createEntity();
		$ret->name = $this->name;
		if ($this->isPersisted()) {
			$ret->setPersisted();
		}
		return $ret;
	}

	protected function equalsImpl(AbstractEntity $other): bool {
		return $this->name === $other->name;
	}

	protected function isSameImpl(AbstractEntity $other): bool {
		return $this->name === $other->name;
	}

	public function getRecipes(): array {
		return $this->wrapper->getRecipes($this);
	}

	public function remove(): void {
		$this->wrapper->remove($this);
	}
}
