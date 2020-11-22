<?php

namespace OCA\Cookbook\Entity\impl;

use Sabre\CalDAV\Exception\InvalidComponentType;

abstract class AbstractEntity {
	
	/**
	 * @var bool
	 */
	private $persisted;
	
	public function __construct($persisted = false)
	{
		$this->persisted = $persisted;
	}
	
	/**
	 * Check if the entity has been saved once
	 * @return bool true, if the element is alreday in the database
	 */
	public function isPersisted(): bool {
		return $this->persisted;
	}
	
	protected function setPersisted(): void {
		$this->persisted = true;
	}
	
	abstract function clone(): AbstractEntity;
	
	public function isSame(AbstractEntity $other): bool {
		if(get_class($this) !== get_class($other))
		{
			throw new InvalidComponentType();
		}
		
		return $this->isSameImpl($other);
	}
	public function equals(AbstractEntity $other): bool {
		if(get_class($this) !== get_class($other))
		{
			throw new InvalidComponentType();
		}
		
		return $this->equalsImpl($other);
	}
	
	abstract protected function isSameImpl(AbstractEntity $other): bool;
	abstract protected function equalsImpl(AbstractEntity $other): bool;
}
