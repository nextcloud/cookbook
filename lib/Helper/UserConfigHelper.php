<?php

namespace OCA\Cookbook\Helper;

use OCP\IConfig;
use OCP\IL10N;

/**
 * This class allows access to the per-user configuration of the app
 */
class UserConfigHelper {
	private string $userId;
	private IConfig $config;
	private IL10N $l;

	public function __construct(
		string $UserId,
		IConfig $config,
		IL10N $l
	) {
		$this->userId = $UserId;
		$this->config = $config;
		$this->l = $l;
	}

	/**
	 * Get a config value from the database
	 *
	 * @param string $key The key to get
	 * @return string The resulting value or '' if the key was not found
	 */
	private function getRawValue(string $key): string {
		return $this->config->getUserValue($this->userId, 'cookbook', $key);
	}

	/**
	 * Set a config value in the database
	 *
	 * @param string $key The key of the configuration
	 * @param string $value The value of the config entry
	 * @return void
	 */
	private function setRawValue(string $key, string $value): void {
		$this->config->setUserValue($this->userId, 'cookbook', $key, $value);
	}

	/**
	 * Get the timestamp of the last rescan of the library
	 *
	 * @return integer The timestamp of the last index rebuild
	 */
	public function getLastIndexUpdate(): int {
		$rawValue = $this->getRawValue('last_index_update');
		if ($rawValue === '') {
			return 0;
		}

		return intval($rawValue);
	}

	/**
	 * Set the timestamp of the last rescan of the library
	 *
	 * @param integer $value The timestamp of the last index rebuild
	 * @return void
	 */
	public function setLastIndexUpdate(int $value): void {
		$this->setRawValue('last_index_update', strval($value));
	}

	/**
	 * Get the number of seconds between rescans of the library
	 *
	 * @return integer The number of seconds to wait before a new rescan is triggered
	 */
	public function getUpdateInterval(): int {
		$rawValue = $this->getRawValue('update_interval');
		if ($rawValue === '') {
			return 5;
		}

		return intval($rawValue);
	}

	/**
	 * Set the interval between the rescan events of the complete library
	 *
	 * @param integer $value The number of seconds to wait at least between rescans
	 * @return void
	 */
	public function setUpdateInterval(int $value): void {
		$this->setRawValue('update_interval', $value);
	}

	/**
	 * Check if the primary imgae should be printed or not
	 *
	 * @return boolean true, if the imgae shoudl be printed
	 */
	public function getPrintImage(): bool {
		$rawValue = $this->getRawValue('print_image');
		if ($rawValue === '') {
			return true;
		}
		return $rawValue === '1';
	}

	/**
	 * Set if the imgae should be printed
	 *
	 * @param boolean $value true if the image should be printed
	 * @return void
	 */
	public function setPrintImage(bool $value): void {
		if ($value) {
			$this->setRawValue('print_image', '1');
		} else {
			$this->setRawValue('print_image', '0');
		}
	}

	/**
	 * Get the name of the default cookbook.
	 *
	 * If no folder is stored in the config yet, a default setting will be generated and saved.
	 *
	 * @return string The name of the folder within the users files
	 */
	public function getFolderName(): string {
		$rawValue = $this->getRawValue('folder');
		if ($rawValue === '') {
			$path = '/' . $this->l->t('Recipes');
			$this->setFolderName($path);
			return $path;
		}

		return $rawValue;
	}

	/**
	 * Set the folder for the user's cookbook.
	 *
	 * @param string $value The name of the folder withon the user's files
	 * @return void
	 */
	public function setFolderName(string $value): void {
		$this->setRawValue('folder', $value);
	}
}
