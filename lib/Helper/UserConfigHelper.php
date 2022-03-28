<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\AppInfo\Application;
use OCP\IConfig;
use OCP\IL10N;

/**
 * This class allows access to the per-user configuration of the app
 */
class UserConfigHelper {
	/**
	 * @var string
	 */
	private $userId;

	/**
	 * @var IConfig
	 */
	private $config;
	
	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(
		string $UserId,
		IConfig $config,
		IL10N $l
	) {
		$this->userId = $UserId;
		$this->config = $config;
		$this->l = $l;
	}

	protected const KEY_LAST_INDEX_UPDATE = 'last_index_update';
	protected const KEY_UPDATE_INTERVAL = 'update_interval';
	protected const KEY_PRINT_IMAGE = 'print_image';
	protected const KEY_FOLDER = 'folder';

	/**
	 * Get a config value from the database
	 *
	 * @param string $key The key to get
	 * @return string The resulting value or '' if the key was not found
	 */
	private function getRawValue(string $key): string {
		return $this->config->getUserValue($this->userId, Application::APP_ID, $key);
	}

	/**
	 * Set a config value in the database
	 *
	 * @param string $key The key of the configuration
	 * @param string $value The value of the config entry
	 * @return void
	 */
	private function setRawValue(string $key, string $value): void {
		$this->config->setUserValue($this->userId, Application::APP_ID, $key, $value);
	}

	/**
	 * Get the timestamp of the last rescan of the library
	 *
	 * @return integer The timestamp of the last index rebuild
	 */
	public function getLastIndexUpdate(): int {
		$rawValue = $this->getRawValue(self::KEY_LAST_INDEX_UPDATE);
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
		$this->setRawValue(self::KEY_LAST_INDEX_UPDATE, strval($value));
	}

	/**
	 * Get the number of seconds between rescans of the library
	 *
	 * @return integer The number of seconds to wait before a new rescan is triggered
	 */
	public function getUpdateInterval(): int {
		$rawValue = $this->getRawValue(self::KEY_UPDATE_INTERVAL);
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
		$this->setRawValue(self::KEY_UPDATE_INTERVAL, $value);
	}

	/**
	 * Check if the primary imgae should be printed or not
	 *
	 * @return boolean true, if the image should be printed
	 */
	public function getPrintImage(): bool {
		$rawValue = $this->getRawValue(self::KEY_PRINT_IMAGE);
		if ($rawValue === '') {
			return true;
		}
		return $rawValue === '1';
	}

	/**
	 * Set if the image should be printed
	 *
	 * @param boolean $value true if the image should be printed
	 * @return void
	 */
	public function setPrintImage(bool $value): void {
		if ($value) {
			$this->setRawValue(self::KEY_PRINT_IMAGE, '1');
		} else {
			$this->setRawValue(self::KEY_PRINT_IMAGE, '0');
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
		$rawValue = $this->getRawValue(self::KEY_FOLDER);

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
	 * @param string $value The name of the folder within the user's files
	 * @return void
	 */
	public function setFolderName(string $value): void {
		$this->setRawValue(self::KEY_FOLDER, $value);
	}
}
