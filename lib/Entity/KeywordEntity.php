<?php

namespace OCA\Cookbook\Entity;

use OCA\Cookbook\Db\KeywordDbWrapper;

class KeywordEntity implements Entity {
	
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
	public function setName($name) {
		$this->name = $name;
	}

	public function persist(): void {
		$this->wrapper->store($this);
	}
}
