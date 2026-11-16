<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace OCA\Cookbook\tests\Unit\Service\Bring;

use OCA\Cookbook\Service\Bring\BringAuth;
use OCA\Cookbook\Service\Bring\BringAuthException;
use OCA\Cookbook\Service\Bring\BringClient;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use PHPUnit\Framework\TestCase;

// The OCP interfaces are provided by the Nextcloud server at runtime and are
// not autoloadable on their own. For unit tests, load them from the reference
// package nextcloud/ocp.
spl_autoload_register(static function (string $class): void {
	$prefix = 'OCP\\';
	if (!str_starts_with($class, $prefix)) {
		return;
	}
	$file = __DIR__ . '/../../../../vendor/nextcloud/ocp/OCP/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
	if (file_exists($file)) {
		require_once $file;
	}
});

class BringClientTest extends TestCase {
	private const BASE_URL = 'https://api.getbring.com/rest/';

	private IClientService $clientService;
	private IClient $client;
	private BringClient $clientUnderTest;

	protected function setUp(): void {
		parent::setUp();
		$this->client = $this->createMock(IClient::class);
		$this->clientService = $this->createMock(IClientService::class);
		$this->clientService->method('newClient')->willReturn($this->client);
		$this->clientUnderTest = new BringClient($this->clientService);
	}

	public function testLoginSuccess(): void {
		$this->client
			->expects($this->once())
			->method('post')
			->with(self::BASE_URL . 'v2/bringauth', $this->callback(static function (array $options): bool {
				return ($options['form_params']['email'] ?? '') === 'user@example.com'
					&& ($options['form_params']['password'] ?? '') === 'secret'
					&& ($options['http_errors'] ?? null) === false;
			}))
			->willReturn($this->mockResponse(200, json_encode([
				'access_token' => 'access-123',
				'refresh_token' => 'refresh-456',
				'uuid' => 'user-uuid',
				'publicUuid' => 'public-uuid',
				'bringListUUID' => 'list-uuid',
			])));

		$this->client
			->expects($this->once())
			->method('get')
			->with(self::BASE_URL . 'v2/bringusers/user-uuid')
			->willReturn($this->mockResponse(200, json_encode([
				'userLocale' => ['country' => 'DE'],
			])));

		$result = $this->clientUnderTest->login('user@example.com', 'secret');

		$this->assertArrayHasKey('auth', $result);
		$this->assertArrayHasKey('refreshToken', $result);
		$this->assertArrayHasKey('defaultListUuid', $result);

		$this->assertInstanceOf(BringAuth::class, $result['auth']);
		$this->assertSame('access-123', $result['auth']->accessToken);
		$this->assertSame('user-uuid', $result['auth']->uuid);
		$this->assertSame('public-uuid', $result['auth']->publicUuid);
		$this->assertSame('DE', $result['auth']->country);

		$this->assertSame('refresh-456', $result['refreshToken']);
		$this->assertSame('list-uuid', $result['defaultListUuid']);
	}

	public function testLoginInvalidCredentials401(): void {
		$this->client
			->method('post')
			->with(self::BASE_URL . 'v2/bringauth')
			->willReturn($this->mockResponse(401, ''));

		$this->expectException(BringAuthException::class);

		$this->clientUnderTest->login('user@example.com', 'wrong-password');
	}

	public function testLoginBadRequest400ThrowsAuthException(): void {
		$this->client
			->method('post')
			->with(self::BASE_URL . 'v2/bringauth')
			->willReturn($this->mockResponse(400, json_encode(['error' => 'Invalid credentials'])));

		$this->expectException(BringAuthException::class);

		$this->clientUnderTest->login('user@example.com', 'wrong-password');
	}

	public function testRefreshToken(): void {
		$this->client
			->expects($this->once())
			->method('post')
			->with(self::BASE_URL . 'v2/bringauth/token', $this->callback(static function (array $options): bool {
				return ($options['form_params']['grant_type'] ?? '') === 'refresh_token'
					&& ($options['form_params']['refresh_token'] ?? '') === 'old-refresh';
			}))
			->willReturn($this->mockResponse(200, json_encode([
				'access_token' => 'new-access',
				'refresh_token' => 'new-refresh',
			])));

		$result = $this->clientUnderTest->refresh('old-refresh');

		$this->assertSame('new-access', $result['accessToken']);
		$this->assertSame('new-refresh', $result['refreshToken']);
	}

	public function testRefreshTokenBadRequest400ThrowsAuthException(): void {
		$this->client
			->expects($this->once())
			->method('post')
			->willReturn($this->mockResponse(400, json_encode(['message' => 'invalid_grant'])));

		$this->expectException(BringAuthException::class);
		$this->expectExceptionCode(400);

		$this->clientUnderTest->refresh('expired-refresh');
	}

	public function testGetLists(): void {
		$auth = new BringAuth('access-123', 'user-uuid', 'public-uuid', 'DE');

		$this->client
			->expects($this->once())
			->method('get')
			->with(self::BASE_URL . 'bringusers/user-uuid/lists', $this->callback(static function (array $options): bool {
				return ($options['headers']['Authorization'] ?? '') === 'Bearer access-123'
					&& ($options['headers']['X-BRING-USER-UUID'] ?? '') === 'user-uuid'
					&& ($options['headers']['X-BRING-PUBLIC-USER-UUID'] ?? '') === 'public-uuid';
			}))
			->willReturn($this->mockResponse(200, json_encode(['lists' => [
				['listUuid' => 'list-1', 'name' => 'Einkauf'],
				['listUuid' => 'list-2', 'name' => 'Vorräte'],
			]])));

		$lists = $this->clientUnderTest->getLists($auth);

		$this->assertSame([
			['listUuid' => 'list-1', 'name' => 'Einkauf'],
			['listUuid' => 'list-2', 'name' => 'Vorräte'],
		], $lists);
	}

	public function testGetItems(): void {
		$auth = new BringAuth('access-123', 'user-uuid', 'public-uuid', 'DE');

		$this->client
			->expects($this->once())
			->method('get')
			->with(self::BASE_URL . 'v2/bringlists/list-uuid')
			->willReturn($this->mockResponse(200, json_encode(['items' => [
				'purchase' => [
					['uuid' => 'p1', 'itemId' => 'Milch', 'specification' => '2 l'],
					['uuid' => 'p2', 'itemId' => 'Eier', 'specification' => '10 Stück'],
				],
				'recently' => [
					['uuid' => 'r1', 'itemId' => 'Butter', 'specification' => '250 g'],
				],
			]])));

		$result = $this->clientUnderTest->getItems($auth, 'list-uuid');

		$this->assertSame([
			['uuid' => 'p1', 'itemId' => 'Milch', 'spec' => '2 l'],
			['uuid' => 'p2', 'itemId' => 'Eier', 'spec' => '10 Stück'],
		], $result['purchase']);
		$this->assertSame([
			['uuid' => 'r1', 'itemId' => 'Butter', 'spec' => '250 g'],
		], $result['recently']);
	}

	public function testAddToPurchasePayloadStructure(): void {
		$auth = new BringAuth('access-123', 'user-uuid', 'public-uuid', 'DE');

		$captured = null;
		$this->client
			->expects($this->once())
			->method('put')
			->with(self::BASE_URL . 'v2/bringlists/list-uuid/items', $this->callback(static function (array $options) use (&$captured): bool {
				$captured = $options;
				return true;
			}))
			->willReturn($this->mockResponse(200, ''));

		$this->clientUnderTest->addToPurchase($auth, 'list-uuid', [
			['itemId' => 'Milch', 'spec' => '2 l'],
			['itemId' => 'Zwiebel', 'spec' => '3 Stück', 'uuid' => 'existing-uuid'],
		]);

		$this->assertIsArray($captured, 'The put() call was not captured.');
		$payload = $captured['json'] ?? [];
		$this->assertCount(2, $payload['changes'] ?? []);

		$first = $payload['changes'][0];
		$this->assertSame('TO_PURCHASE', $first['operation']);
		$this->assertSame('Milch', $first['itemId']);
		$this->assertSame('2 l', $first['spec']);
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
			$first['uuid'],
			'A new entry must receive a generated version 4 UUID.'
		);

		$second = $payload['changes'][1];
		$this->assertSame('TO_PURCHASE', $second['operation']);
		$this->assertSame('existing-uuid', $second['uuid']);
	}

	private function mockResponse(int $status, string $body): IResponse {
		$response = $this->createMock(IResponse::class);
		$response->method('getStatusCode')->willReturn($status);
		$response->method('getBody')->willReturn($body);
		return $response;
	}
}
