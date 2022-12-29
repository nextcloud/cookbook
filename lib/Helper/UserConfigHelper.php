<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\AppInfo\Application;
use OCA\Cookbook\Exception\UserNotLoggedInException;
use OCP\IConfig;
use OCP\IL10N;

/**
 * This class allows access to the per-user configuration of the app
 */
class UserConfigHelper {
	/**
	 * @var ?string
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
		?string $UserId,
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
	protected const KEY_VISIBLE_INFO_BLOCKS = 'visible_info_blocks';
	protected const KEY_FOLDER = 'folder';

	/**
	 * Checks if the user is logged in and the configuration can be obtained at all
	 *
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	private function ensureUserIsLoggedIn(): void {
		if (is_null($this->userId)) {
			throw new UserNotLoggedInException($this->l->t('The user is not logged in. No user configuration can be obtained.'));
		}
	}

	/**
	 * Get a config value from the database
	 *
	 * @param string $key The key to get
	 * @return string The resulting value or '' if the key was not found
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	private function getRawValue(string $key): string {
		$this->ensureUserIsLoggedIn();
		return $this->config->getUserValue($this->userId, Application::APP_ID, $key);
	}

	/**
	 * Set a config value in the database
	 *
	 * @param string $key The key of the configuration
	 * @param string $value The value of the config entry
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	private function setRawValue(string $key, string $value): void {
		$this->ensureUserIsLoggedIn();
		$this->config->setUserValue($this->userId, Application::APP_ID, $key, $value);
	}

	/**
	 * Get the timestamp of the last rescan of the library
	 *
	 * @return int The timestamp of the last index rebuild
	 * @throws UserNotLoggedInException if no user is logged in
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
	 * @param int $value The timestamp of the last index rebuild
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setLastIndexUpdate(int $value): void {
		$this->setRawValue(self::KEY_LAST_INDEX_UPDATE, strval($value));
	}

	/**
	 * Get the number of seconds between rescans of the library
	 *
	 * @return int The number of seconds to wait before a new rescan is triggered
	 * @throws UserNotLoggedInException if no user is logged in
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
	 * @param int $value The number of seconds to wait at least between rescans
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setUpdateInterval(int $value): void {
		$this->setRawValue(self::KEY_UPDATE_INTERVAL, $value);
	}

	/**
	 * Check if the primary imgae should be printed or not
	 *
	 * @return bool true, if the image should be printed
	 * @throws UserNotLoggedInException if no user is logged in
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
	 * @param bool $value true if the image should be printed
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setPrintImage(bool $value): void {
		if ($value) {
			$this->setRawValue(self::KEY_PRINT_IMAGE, '1');
		} else {
			$this->setRawValue(self::KEY_PRINT_IMAGE, '0');
		}
	}

	/**
	 * Determines which info blocks are displayed next to the recipe
	 *
	 * @return array<string, bool> keys: info block ids, values: display state
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function getVisibleInfoBlocks(): array {
		$rawValue = $this->getRawValue(self::KEY_VISIBLE_INFO_BLOCKS);

		if ($rawValue === '') {
			return [
				'preparation-time' => true,
				'cooking-time' => true,
				'total-time' => true,
				'nutrition-information' => true,
				'tools' => true,
			];
		}

		return json_decode($rawValue, true);
	}

	/**
	 * Sets which info blocks are displayed next to the recipe
	 *
	 * @param array<string, bool> keys: info block ids, values: display state
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setVisibleInfoBlocks(array $visibleInfoBlocks): void {
		$this->setRawValue(self::KEY_VISIBLE_INFO_BLOCKS, json_encode($visibleInfoBlocks));
	}

	/**
	 * Get the name of the default cookbook.
	 *
	 * If no folder is stored in the config yet, a default setting will be generated and saved.
	 *
	 * **Note:**
	 * Do not use this method directly.
	 * Instead use the methods of the UserFolderHelper class
	 *
	 * @return string The name of the folder within the users files
	 * @throws UserNotLoggedInException if no user is logged in
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
	 * **Note:**
	 * Do not use this method directly.
	 * Instead use the methods of the UserFolderHelper class
	 *
	 * @param string $value The name of the folder within the user's files
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setFolderName(string $value): void {
		$this->setRawValue(self::KEY_FOLDER, $value);
	}
}
