<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\AppInfo\Application;
use OCA\Cookbook\Exception\UserNotLoggedInException;
use NCU\Config\IUserConfig;
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
	 * @var IUserConfig
	 */
	private $config;

	/**
	 * @var IL10N
	 */
	private $l;

	public function __construct(
		?string $UserId,
		IUserConfig $config,
		IL10N $l,
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
	protected const KEY_BROWSERLESS_CONFIG = 'browserless_config';

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
	 * Get the timestamp of the last rescan of the library
	 *
	 * @return int The timestamp of the last index rebuild
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function getLastIndexUpdate(): int {
		$this->ensureUserIsLoggedIn();
		return $this->config->getValueInt($this->userId, Application::APP_ID, self::KEY_LAST_INDEX_UPDATE, 0);
	}

	/**
	 * Set the timestamp of the last rescan of the library
	 *
	 * @param int $value The timestamp of the last index rebuild
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setLastIndexUpdate(int $value): void {
		$this->ensureUserIsLoggedIn();
		$this->config->setValueInt($this->userId, Application::APP_ID, self::KEY_LAST_INDEX_UPDATE, $value);
	}

	/**
	 * Get the number of seconds between rescans of the library
	 *
	 * @return int The number of seconds to wait before a new rescan is triggered
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function getUpdateInterval(): int {
		$this->ensureUserIsLoggedIn();
		return $this->config->getValueInt($this->userId, Application::APP_ID, self::KEY_UPDATE_INTERVAL, 5);
	}

	/**
	 * Set the interval between the rescan events of the complete library
	 *
	 * @param int $value The number of seconds to wait at least between rescans
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setUpdateInterval(int $value): void {
		$this->ensureUserIsLoggedIn();
		$this->config->setValueInt($this->userId, Application::APP_ID, self::KEY_UPDATE_INTERVAL, $value);
	}

	/**
	 * Check if the primary imgae should be printed or not
	 *
	 * @return bool true, if the image should be printed
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function getPrintImage(): bool {
		$this->ensureUserIsLoggedIn();
		return $this->config->getValueBool($this->userId, Application::APP_ID, self::KEY_PRINT_IMAGE, true);
	}

	/**
	 * Set if the image should be printed
	 *
	 * @param bool $value true if the image should be printed
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setPrintImage(bool $value): void {
		$this->ensureUserIsLoggedIn();
		$this->config->setValueBool($this->userId, Application::APP_ID, self::KEY_PRINT_IMAGE, $value);
	}

	/**
	 * Determines which info blocks are displayed next to the recipe
	 *
	 * @return array<string, bool> keys: info block ids, values: display state
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function getVisibleInfoBlocks(): array {
		$this->ensureUserIsLoggedIn();
		$default = [
			'preparation-time' => true,
			'cooking-time' => true,
			'total-time' => true,
			'nutrition-information' => true,
			'tools' => true,
		];
		return $this->config->getValueArray($this->userId, Application::APP_ID, self::KEY_VISIBLE_INFO_BLOCKS, $default);
	}

	/**
	 * Sets which info blocks are displayed next to the recipe
	 *
	 * @param array<string, bool> keys: info block ids, values: display state
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setVisibleInfoBlocks(array $visibleInfoBlocks): void {
		$this->ensureUserIsLoggedIn();
		$this->config->setValueArray($this->userId, Application::APP_ID, self::KEY_VISIBLE_INFO_BLOCKS, $visibleInfoBlocks);
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
		$this->ensureUserIsLoggedIn();
		$rawValue = $this->config->getValueString($this->userId, Application::APP_ID, self::KEY_FOLDER, '');

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
		$this->ensureUserIsLoggedIn();
		$this->config->setValueString($this->userId, Application::APP_ID, self::KEY_FOLDER, $value);
	}

	/**
	 * Gets the browserless config from the configuration
	 *
	 * @return array<string, string | null> keys: url and token, values: url and token
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function getBrowserlessConfig(): array {
		$this->ensureUserIsLoggedIn();
		$default = [
			'url' => null,
			'token' => null,
		];
		return $this->config->getValueArray($this->userId, Application::APP_ID, self::KEY_BROWSERLESS_CONFIG, $default);
	}

	/**
	 * Sets the browserless config in the configuration
	 *
	 * @param array<string, bool> keys: url and token, values: url and token
	 * @throws UserNotLoggedInException if no user is logged in
	 */
	public function setBrowserlessConfig(array $data): void {
		$this->ensureUserIsLoggedIn();
		$this->config->setValueArray($this->userId, Application::APP_ID, self::KEY_BROWSERLESS_CONFIG, $data, flags: IUserConfig::FLAG_SENSITIVE);
	}
}
