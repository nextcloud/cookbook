<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service\Bring;

use OCA\Cookbook\AppInfo\Application;
use OCP\IConfig;
use OCP\Security\ICrypto;

/**
 * Stores the Bring! connection of a Nextcloud user.
 *
 * The whole record is encrypted. Neither the password nor an access token is stored.
 */
class BringSessionStore {
	private const CONFIG_KEY = 'bring_session';

	public function __construct(
		private IConfig $config,
		private ICrypto $crypto,
	) {
	}

	/**
	 * Load the connection of a user.
	 *
	 * @return array{email: string, refreshToken: string, uuid: string, publicUuid: string, country: string, defaultListUuid: string}|null
	 *                                                                                                                                     null if the user is not connected or the stored record cannot be decrypted
	 */
	public function load(string $userId): ?array {
		$stored = $this->config->getUserValue($userId, Application::APP_ID, self::CONFIG_KEY, '');
		if ($stored === '') {
			return null;
		}

		try {
			$data = json_decode($this->crypto->decrypt($stored), true, 512, JSON_THROW_ON_ERROR);
		} catch (\Throwable $e) {
			return null;
		}

		if (!is_array($data)) {
			return null;
		}

		$email = (string)($data['email'] ?? '');
		$refreshToken = (string)($data['refreshToken'] ?? '');
		$uuid = (string)($data['uuid'] ?? '');
		$publicUuid = (string)($data['publicUuid'] ?? '');
		$country = (string)($data['country'] ?? '');
		$defaultListUuid = (string)($data['defaultListUuid'] ?? '');

		if ($refreshToken === '' || $uuid === '' || $publicUuid === '' || $country === '') {
			return null;
		}

		return [
			'email' => $email,
			'refreshToken' => $refreshToken,
			'uuid' => $uuid,
			'publicUuid' => $publicUuid,
			'country' => $country,
			'defaultListUuid' => $defaultListUuid,
		];
	}

	/**
	 * Store a new connection, replacing any existing one.
	 */
	public function saveConnection(
		string $userId,
		string $email,
		string $refreshToken,
		string $uuid,
		string $publicUuid,
		string $country,
		string $defaultListUuid,
	): void {
		$this->write($userId, [
			'email' => $email,
			'refreshToken' => $refreshToken,
			'uuid' => $uuid,
			'publicUuid' => $publicUuid,
			'country' => $country,
			'defaultListUuid' => $defaultListUuid,
		]);
	}

	/**
	 * Replace the refresh token of an existing connection. Does nothing if the user is not connected.
	 */
	public function updateRefreshToken(string $userId, string $refreshToken): void {
		$session = $this->load($userId);
		if ($session === null) {
			return;
		}

		$session['refreshToken'] = $refreshToken;
		$this->write($userId, $session);
	}

	/**
	 * Remove the connection of a user.
	 */
	public function clear(string $userId): void {
		$this->config->deleteUserValue($userId, Application::APP_ID, self::CONFIG_KEY);
	}

	/**
	 * @param array<string, string> $session
	 */
	private function write(string $userId, array $session): void {
		$this->config->setUserValue(
			$userId,
			Application::APP_ID,
			self::CONFIG_KEY,
			$this->crypto->encrypt(json_encode($session, JSON_THROW_ON_ERROR)),
		);
	}
}
