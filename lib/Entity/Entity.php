<?php

namespace OCA\Cookbook\Entity;

interface Entity {
	/**
	 * Store the Entity to the database
	 */
	public function persist(): void;
	public function remove(): void;
}
