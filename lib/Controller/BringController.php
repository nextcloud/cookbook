<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Controller;

use OCA\Cookbook\Service\Bring\BringApiException;
use OCA\Cookbook\Service\Bring\BringAuth;
use OCA\Cookbook\Service\Bring\BringAuthException;
use OCA\Cookbook\Service\Bring\BringClient;
use OCA\Cookbook\Service\Bring\BringMergeService;
use OCA\Cookbook\Service\Bring\BringSessionStore;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IL10N;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

/**
 * Endpoints of the Bring! export used by the web UI.
 */
class BringController extends Controller {
	private const MAX_ITEMS = 200;
	private const MAX_LINE_LENGTH = 500;

	public function __construct(
		string $AppName,
		IRequest $request,
		private ?string $UserId,
		private IL10N $l,
		private LoggerInterface $logger,
		private BringSessionStore $sessionStore,
		private BringClient $client,
		private BringMergeService $merge,
	) {
		parent::__construct($AppName, $request);
	}

	/**
	 * Report whether the current user is connected to Bring!.
	 */
	#[NoAdminRequired]
	public function status(): DataResponse {
		$userId = $this->UserId;
		if ($userId === null) {
			return $this->notLoggedIn();
		}

		$session = $this->sessionStore->load($userId);
		if ($session === null) {
			return new DataResponse(['connected' => false]);
		}

		return new DataResponse([
			'connected' => true,
			'email' => $session['email'],
		]);
	}

	/**
	 * Log in to Bring! and store the connection.
	 */
	#[NoAdminRequired]
	public function login(string $email, string $password): DataResponse {
		$userId = $this->UserId;
		if ($userId === null) {
			return $this->notLoggedIn();
		}

		$email = trim($email);
		if ($email === '' || $password === '') {
			return $this->error(
				Http::STATUS_BAD_REQUEST,
				$this->l->t('Please enter the email address and the password of your Bring! account.'),
			);
		}

		try {
			$result = $this->client->login($email, $password);
		} catch (BringAuthException $e) {
			return $this->error(
				Http::STATUS_UNAUTHORIZED,
				$this->l->t('Bring! did not accept the email address or the password.'),
			);
		} catch (BringApiException $e) {
			return $this->apiFailure($e);
		}

		/** @var BringAuth $auth */
		$auth = $result['auth'];
		$this->sessionStore->saveConnection(
			$userId,
			$email,
			$result['refreshToken'],
			$auth->uuid,
			$auth->publicUuid,
			$auth->country,
			$result['defaultListUuid'],
		);

		return new DataResponse([
			'connected' => true,
			'email' => $email,
		]);
	}

	/**
	 * Remove the Bring! connection of the current user.
	 */
	#[NoAdminRequired]
	public function disconnect(): DataResponse {
		$userId = $this->UserId;
		if ($userId === null) {
			return $this->notLoggedIn();
		}

		$this->sessionStore->clear($userId);

		return new DataResponse(['connected' => false]);
	}

	/**
	 * Get the shopping lists of the connected account.
	 */
	#[NoAdminRequired]
	public function lists(): DataResponse {
		return $this->withBring(fn (BringAuth $auth, array $session): array => [
			'lists' => $this->client->getLists($auth),
			'defaultListUuid' => $session['defaultListUuid'],
		]);
	}

	/**
	 * Show what exporting the ingredients to a list would change, without writing anything.
	 *
	 * @param array<mixed> $items ingredient lines: `line`, optionally `name` (chosen alternative) and `enabled`
	 * @param string $mode `merge` or `separate`
	 */
	#[NoAdminRequired]
	public function preview(
		string $listUuid,
		array $items,
		string $recipeName = '',
		string $mode = BringMergeService::MODE_MERGE,
	): DataResponse {
		$lines = $this->prepareLines($listUuid, $items, $recipeName, $mode);
		if ($lines instanceof DataResponse) {
			return $lines;
		}

		return $this->withBring(function (BringAuth $auth) use ($lines, $listUuid, $recipeName, $mode): array {
			$list = $this->client->getItems($auth, $listUuid);
			$rows = $this->merge->plan($lines, $list, $recipeName, $mode);

			return ['rows' => $this->clientRows($rows)];
		});
	}

	/**
	 * Write the enabled ingredients to a list. The plan is computed again from the current list.
	 *
	 * @param array<mixed> $items ingredient lines: `line`, optionally `name` (chosen alternative) and `enabled`
	 * @param string $mode `merge` or `separate`
	 */
	#[NoAdminRequired]
	public function export(
		string $listUuid,
		array $items,
		string $recipeName = '',
		string $mode = BringMergeService::MODE_MERGE,
	): DataResponse {
		$lines = $this->prepareLines($listUuid, $items, $recipeName, $mode);
		if ($lines instanceof DataResponse) {
			return $lines;
		}

		return $this->withBring(function (BringAuth $auth) use ($lines, $listUuid, $recipeName, $mode): array {
			$list = $this->client->getItems($auth, $listUuid);
			$rows = $this->merge->plan($lines, $list, $recipeName, $mode);

			$changes = [];
			foreach ($rows as $row) {
				if ($this->merge->isWrite($row['action'])) {
					// Bring! keeps one entry per name, so a later row for the same name replaces an earlier one.
					$changes[mb_strtolower($row['itemId'])] = [
						'itemId' => $row['itemId'],
						'spec' => $row['resultSpec'],
						'uuid' => $row['uuid'],
					];
				}
			}
			$changes = array_values($changes);

			$this->client->addToPurchase($auth, $listUuid, $changes);

			return [
				'written' => count($changes),
				'rows' => $this->clientRows($rows),
			];
		});
	}

	/**
	 * Run an action with a fresh Bring! access token and translate failures into responses.
	 *
	 * The stored connection is only removed if the refresh token itself is rejected.
	 *
	 * @param callable(BringAuth, array<string, string>): array<mixed> $action
	 */
	private function withBring(callable $action): DataResponse {
		$userId = $this->UserId;
		if ($userId === null) {
			return $this->notLoggedIn();
		}

		$session = $this->sessionStore->load($userId);
		if ($session === null) {
			return $this->reconnectRequired($this->l->t('You are not connected to Bring!.'));
		}

		try {
			$tokens = $this->client->refresh($session['refreshToken']);
		} catch (BringAuthException $e) {
			$this->sessionStore->clear($userId);

			return $this->reconnectRequired($this->l->t('Your Bring! connection has expired. Please connect again.'));
		} catch (BringApiException $e) {
			return $this->apiFailure($e);
		}

		if ($tokens['refreshToken'] !== $session['refreshToken']) {
			$this->sessionStore->updateRefreshToken($userId, $tokens['refreshToken']);
		}

		$auth = new BringAuth(
			$tokens['accessToken'],
			$session['uuid'],
			$session['publicUuid'],
			$session['country'],
		);

		try {
			return new DataResponse($action($auth, $session));
		} catch (BringAuthException $e) {
			return $this->reconnectRequired($this->l->t('Bring! did not accept the request. Please connect again.'));
		} catch (BringApiException $e) {
			return $this->apiFailure($e);
		}
	}

	private function apiFailure(BringApiException $e): DataResponse {
		if ($e->getCode() === Http::STATUS_NOT_FOUND) {
			return $this->error(Http::STATUS_NOT_FOUND, $this->l->t('The Bring! list was not found.'));
		}

		$this->logger->warning('Bring! request failed: ' . $e->getMessage(), ['exception' => $e]);

		return $this->error(
			Http::STATUS_BAD_GATEWAY,
			$this->l->t('Bring! could not be reached or answered unexpectedly. Please try again later.'),
		);
	}

	/**
	 * Validate the request and reduce the items to what the merge service needs.
	 *
	 * @param array<mixed> $items
	 * @return list<array{line: string, name?: string, enabled?: bool}>|DataResponse a 400 response if the request is invalid
	 */
	private function prepareLines(string $listUuid, array $items, string $recipeName, string $mode): array|DataResponse {
		$valid = preg_match('~^[0-9a-fA-F-]{8,64}$~', $listUuid) === 1
			&& trim($recipeName) !== ''
			&& in_array($mode, [BringMergeService::MODE_MERGE, BringMergeService::MODE_SEPARATE], true)
			&& $items !== []
			&& count($items) <= self::MAX_ITEMS;

		$lines = [];
		if ($valid) {
			foreach ($items as $item) {
				if (!is_array($item) || !isset($item['line']) || !is_string($item['line'])
					|| mb_strlen($item['line']) > self::MAX_LINE_LENGTH) {
					$valid = false;
					break;
				}

				$line = ['line' => $item['line']];
				if (isset($item['name']) && is_string($item['name'])) {
					$line['name'] = $item['name'];
				}
				if (isset($item['enabled']) && is_bool($item['enabled'])) {
					$line['enabled'] = $item['enabled'];
				}
				$lines[] = $line;
			}
		}

		if (!$valid) {
			return $this->error(Http::STATUS_BAD_REQUEST, $this->l->t('The request is incomplete or invalid.'));
		}

		return $lines;
	}

	/**
	 * Remove the internal item uuid before the rows go to the browser.
	 *
	 * @param list<array<string, mixed>> $rows
	 * @return list<array<string, mixed>>
	 */
	private function clientRows(array $rows): array {
		return array_map(static function (array $row): array {
			unset($row['uuid']);

			return $row;
		}, $rows);
	}

	private function notLoggedIn(): DataResponse {
		return $this->error(Http::STATUS_UNAUTHORIZED, $this->l->t('You are not logged in.'));
	}

	private function reconnectRequired(string $message): DataResponse {
		return $this->error(Http::STATUS_UNAUTHORIZED, $message, true);
	}

	private function error(int $status, string $message, bool $reconnect = false): DataResponse {
		return new DataResponse(['message' => $message, 'reconnect' => $reconnect], $status);
	}
}
