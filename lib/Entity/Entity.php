<?php

namespace OCA\Cookbook\Entity;

interface Entity {
	/**
	 * Store the Entity to the database
	 */
	public function persist(): void;
	
	/**
	 * Remove the Entity immeadiately from the database
	 */
	public function remove(): void;
	
	/**
	 * Reload all values from the database
	 */
	public function reload(): void;
}
