<?php

namespace OCA\Cookbook\Db;

use OCP\IDBConnection;

abstract class AbstractDbWrapper {
	
	/**
	 * @var array
	 */
	private $cache;
	
	/**
	 * @var bool
	 */
	private $initialized;
	
	/**
	 * @var IDBConnection
	 */
	private $db;
	
	public function __construct(IDBConnection $db) {
		$this->initialized = false;
		$this->db = $db;
	}
	
	/**
	 * Fetch all elements from the database.
	 *
	 * The concrete class must implemnt a way to fetch an array or elements represented by the database.
	 * @return array The (possible empty) array of entities in the database
	 */
	abstract protected function fetchDatabase(): array;
	
	/**
	 * Fetch the entries in the database
	 *
	 * If the cache has already been fetched, the values in the cache are returned.
	 *
	 * @return array The entities in the database.
	 */
	protected function getEntries(): array {
		if (! $this->initialized) {
			$this->reloadCache();
		}
		
		return $this->cache;
	}
	
	/**
	 * Reload the cache from the database.
	 */
	private function reloadCache(): void {
		$this->cache = $this->fetchDatabase();
		$this->initialized = true;
	}
	
	/**
	 * Invalidate the local cache.
	 *
	 * This will cause any access to the entities to refetch the whole cache.
	 */
	protected function invalidateCache(): void {
		$this->initialized = false;
	}
	
	/**
	 * Set the cache to the given array.
	 *
	 * This function will repace the current cache by the given array.
	 * No further checks are carried out.
	 * Please be very careful to provide the most up to date data as present in the database.
	 * Otherwise you will see very strange effects.
	 *
	 * @param array $entities The new entities of the cache
	 */
	protected function setEntites(array $entities): void {
		$this->cache = $entities;
		$this->initialized = true;
	}
}
