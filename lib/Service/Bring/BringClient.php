<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service\Bring;

use OCP\Http\Client\IClientService;

/**
 * Client for the unofficial Bring! REST API.
 *
 * The endpoints, headers and field names follow the open-source library
 * miaucl/bring-api and were verified against a real account.
 */
class BringClient {
	private const BASE_URL = 'https://api.getbring.com/rest/';

	/**
	 * Public identification values, as used by miaucl/bring-api.
	 * They identify the client, they are not a secret.
	 */
	private const API_KEY = 'cof4Nc6D8saplXjE3h3HXqHH8m7VU2i1Gs0g85Sp';
	private const CLIENT_ID = 'android';
	private const APPLICATION_ID = 'bring';

	private const TIMEOUT_SECONDS = 15;
	private const DEFAULT_COUNTRY = 'DE';

	public function __construct(
		private IClientService $clientService,
	) {
	}

	/**
	 * Log in with email and password.
	 *
	 * @return array{auth: BringAuth, refreshToken: string, defaultListUuid: string}
	 * @throws BringAuthException if Bring! rejects the credentials
	 * @throws BringApiException on any other failure
	 */
	public function login(string $email, string $password): array {
		try {
			$data = $this->send('POST', 'v2/bringauth', [
				'form_params' => [
					'email' => $email,
					'password' => $password,
				],
			]);
		} catch (BringApiException $e) {
			if ($e->getCode() !== 400) {
				throw $e;
			}
			throw new BringAuthException('Bring! rejected the credentials.', 400, $e);
		}

		foreach (['access_token', 'refresh_token', 'uuid', 'publicUuid'] as $field) {
			if (!isset($data[$field]) || !is_string($data[$field]) || $data[$field] === '') {
				throw new BringApiException('The Bring! login response lacks the field ' . $field . '.');
			}
		}

		$auth = new BringAuth(
			$data['access_token'],
			$data['uuid'],
			$data['publicUuid'],
			self::DEFAULT_COUNTRY,
		);
		$auth->country = $this->fetchCountry($auth);

		$defaultListUuid = isset($data['bringListUUID']) && is_string($data['bringListUUID'])
			? $data['bringListUUID']
			: '';

		return [
			'auth' => $auth,
			'refreshToken' => $data['refresh_token'],
			'defaultListUuid' => $defaultListUuid,
		];
	}

	/**
	 * Exchange a refresh token for a new access token.
	 *
	 * Bring! currently returns the same refresh token. If a different one is
	 * returned, the caller has to persist it.
	 *
	 * @return array{accessToken: string, refreshToken: string}
	 * @throws BringAuthException if the refresh token is invalid or expired
	 * @throws BringApiException on any other failure
	 */
	public function refresh(string $refreshToken): array {
		$data = $this->send('POST', 'v2/bringauth/token', [
			'form_params' => [
				'grant_type' => 'refresh_token',
				'refresh_token' => $refreshToken,
			],
		]);

		if (!isset($data['access_token']) || !is_string($data['access_token']) || $data['access_token'] === '') {
			throw new BringApiException('The Bring! refresh response lacks an access token.');
		}

		$newRefreshToken = isset($data['refresh_token']) && is_string($data['refresh_token']) && $data['refresh_token'] !== ''
			? $data['refresh_token']
			: $refreshToken;

		return [
			'accessToken' => $data['access_token'],
			'refreshToken' => $newRefreshToken,
		];
	}

	/**
	 * Get the shopping lists of the user.
	 *
	 * @return list<array{listUuid: string, name: string}>
	 * @throws BringAuthException
	 * @throws BringApiException
	 */
	public function getLists(BringAuth $auth): array {
		$data = $this->send('GET', 'bringusers/' . rawurlencode($auth->uuid) . '/lists', [], $auth);

		if (!isset($data['lists']) || !is_array($data['lists'])) {
			throw new BringApiException('The Bring! list overview has an unexpected format.');
		}

		$lists = [];
		foreach ($data['lists'] as $list) {
			if (is_array($list) && isset($list['listUuid']) && is_string($list['listUuid'])) {
				$lists[] = [
					'listUuid' => $list['listUuid'],
					'name' => isset($list['name']) ? (string)$list['name'] : '',
				];
			}
		}

		return $lists;
	}

	/**
	 * Get the current content of a list.
	 *
	 * Entries of `recently` were checked off and are not on the shopping list anymore.
	 *
	 * @return array{
	 *     purchase: list<array{uuid: string, itemId: string, spec: string}>,
	 *     recently: list<array{uuid: string, itemId: string, spec: string}>
	 * }
	 * @throws BringAuthException
	 * @throws BringApiException
	 */
	public function getItems(BringAuth $auth, string $listUuid): array {
		$data = $this->send('GET', 'v2/bringlists/' . rawurlencode($listUuid), [], $auth);

		$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];

		return [
			'purchase' => $this->normalizeItems($items['purchase'] ?? []),
			'recently' => $this->normalizeItems($items['recently'] ?? []),
		];
	}

	/**
	 * Put items on the shopping list of a list.
	 *
	 * An item whose itemId already exists is updated, a new itemId creates an entry.
	 * To rename an existing entry, pass its uuid together with the new itemId.
	 *
	 * @param list<array{itemId: string, spec: string, uuid?: string}> $items
	 * @throws BringAuthException
	 * @throws BringApiException
	 */
	public function addToPurchase(BringAuth $auth, string $listUuid, array $items): void {
		if ($items === []) {
			return;
		}

		$changes = [];
		foreach ($items as $item) {
			$changes[] = [
				'operation' => 'TO_PURCHASE',
				'itemId' => $item['itemId'],
				'spec' => $item['spec'],
				'uuid' => ($item['uuid'] ?? '') !== '' ? $item['uuid'] : $this->generateUuid(),
				'accuracy' => '0.0',
				'altitude' => '0.0',
				'latitude' => '0.0',
				'longitude' => '0.0',
			];
		}

		$this->send('PUT', 'v2/bringlists/' . rawurlencode($listUuid) . '/items', [
			'json' => [
				'changes' => $changes,
				'sender' => '',
			],
		], $auth);
	}

	/**
	 * Read the country of the account. Bring! sends it in the user profile, not in the login response.
	 * Falls back to the default if the profile cannot be read.
	 */
	private function fetchCountry(BringAuth $auth): string {
		try {
			$data = $this->send('GET', 'v2/bringusers/' . rawurlencode($auth->uuid), [], $auth);
		} catch (BringApiException $e) {
			return self::DEFAULT_COUNTRY;
		}

		$country = $data['userLocale']['country'] ?? null;

		return is_string($country) && $country !== '' ? $country : self::DEFAULT_COUNTRY;
	}

	/**
	 * @param mixed $raw
	 * @return list<array{uuid: string, itemId: string, spec: string}>
	 */
	private function normalizeItems($raw): array {
		$items = [];
		if (!is_array($raw)) {
			return $items;
		}

		foreach ($raw as $entry) {
			if (!is_array($entry) || !isset($entry['uuid'], $entry['itemId'])) {
				continue;
			}
			$items[] = [
				'uuid' => (string)$entry['uuid'],
				'itemId' => (string)$entry['itemId'],
				// Bring! calls the field `specification` in responses, but `spec` in requests.
				'spec' => (string)($entry['specification'] ?? ''),
			];
		}

		return $items;
	}

	/**
	 * @return array<string, string>
	 */
	private function buildHeaders(?BringAuth $auth): array {
		$headers = [
			'Accept' => 'application/json',
			'X-BRING-API-KEY' => self::API_KEY,
			'X-BRING-CLIENT' => self::CLIENT_ID,
			'X-BRING-APPLICATION' => self::APPLICATION_ID,
		];

		if ($auth !== null) {
			$headers['Authorization'] = 'Bearer ' . $auth->accessToken;
			$headers['X-BRING-USER-UUID'] = $auth->uuid;
			$headers['X-BRING-PUBLIC-USER-UUID'] = $auth->publicUuid;
			$headers['X-BRING-COUNTRY'] = $auth->country;
		}

		return $headers;
	}

	/**
	 * Send a request and return the decoded JSON object.
	 *
	 * An empty body (as sent by Bring! after a successful write) yields an empty array.
	 * The response body is never put into an exception message.
	 *
	 * @param array<string, mixed> $options
	 * @return array<mixed>
	 * @throws BringAuthException
	 * @throws BringApiException
	 */
	private function send(string $method, string $path, array $options = [], ?BringAuth $auth = null): array {
		$options['headers'] = $this->buildHeaders($auth);
		$options['timeout'] = self::TIMEOUT_SECONDS;
		// Evaluate the status code ourselves instead of getting an exception per HTTP error.
		$options['http_errors'] = false;

		$client = $this->clientService->newClient();
		$url = self::BASE_URL . $path;

		try {
			$response = match ($method) {
				'GET' => $client->get($url, $options),
				'POST' => $client->post($url, $options),
				'PUT' => $client->put($url, $options),
				default => throw new \InvalidArgumentException('Unsupported HTTP method ' . $method),
			};
			$status = $response->getStatusCode();
			$body = $response->getBody();
		} catch (\InvalidArgumentException $e) {
			throw $e;
		} catch (\Exception $e) {
			throw new BringApiException('The Bring! API could not be reached.', 0, $e);
		}

		if (is_resource($body)) {
			$body = stream_get_contents($body);
		}
		$body = is_string($body) ? $body : '';

		if ($status === 401) {
			throw new BringAuthException('Bring! rejected the credentials or the token.', 401);
		}

		// Observed: Bring! answers a missing or invalid Authorization header with an HTML page and status 400.
		if ($status === 400 && $auth !== null && !$this->isJson($body)) {
			throw new BringAuthException('Bring! did not accept the authorization.', 400);
		}

		if ($status < 200 || $status >= 300) {
			throw new BringApiException('The Bring! API answered with HTTP ' . $status . '.', $status);
		}

		if (trim($body) === '') {
			return [];
		}

		$data = json_decode($body, true);
		if (!is_array($data)) {
			throw new BringApiException('The Bring! API returned an invalid response.', $status);
		}

		return $data;
	}

	private function isJson(string $body): bool {
		json_decode($body);

		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Generate a random version 4 UUID for a new list entry.
	 */
	private function generateUuid(): string {
		$bytes = random_bytes(16);
		$bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
		$bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
	}
}
