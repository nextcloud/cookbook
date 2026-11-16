<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service\Bring;

/**
 * Decides what is written to a Bring! list for the ingredient lines of a recipe.
 *
 * This is pure logic without any HTTP access. Bring! replaces an entry that has the same
 * name (`itemId`), so existing entries must be read first and merged here.
 *
 * Merge mode: an ingredient that is already on the list is summed into the existing entry if
 * both quantities are plain numbers with the same unit. If it cannot be summed, a separate entry
 * named "Name (Recipe)" is written instead. Separate mode always writes "Name (Recipe)" entries.
 */
class BringMergeService {
	public const MODE_MERGE = 'merge';
	public const MODE_SEPARATE = 'separate';

	public const ACTION_ADD = 'add';
	public const ACTION_READD = 'readd';
	public const ACTION_SUM = 'sum';
	public const ACTION_UPDATE = 'update';
	public const ACTION_UNCHANGED = 'unchanged';
	public const ACTION_SKIPPED = 'skipped';

	/**
	 * Without a unit, an amount counts as a number of pieces.
	 */
	private const PIECE_UNIT = 'stück';

	/**
	 * Used in the name of a separate entry if the recipe has no name.
	 */
	private const FALLBACK_LABEL = 'Cookbook';

	public function __construct(
		private BringItemParser $parser,
	) {
	}

	/**
	 * Plan the changes for the ingredient lines of a recipe.
	 *
	 * Header and empty lines produce no row. A line is enabled unless the caller disables it;
	 * if the caller says nothing, a bare staple (for example "Salz") is disabled.
	 * A later line with the same name sees the result of the earlier lines.
	 *
	 * @param list<array{line: string, name?: string, enabled?: bool}> $lines
	 *                                                                        `name` selects one of the alternatives of the line
	 * @param array{
	 *     purchase: list<array{uuid: string, itemId: string, spec: string}>,
	 *     recently: list<array{uuid: string, itemId: string, spec: string}>
	 * } $list the current content of the Bring! list
	 * @return list<array{
	 *     index: int,
	 *     original: string,
	 *     name: string,
	 *     alternatives: list<string>,
	 *     isStaple: bool,
	 *     enabled: bool,
	 *     action: string,
	 *     itemId: string,
	 *     uuid: string,
	 *     existingSpec: string|null,
	 *     resultSpec: string
	 * }>
	 */
	public function plan(array $lines, array $list, string $recipeName, string $mode): array {
		$purchase = $this->index($list['purchase']);
		$recently = $this->index($list['recently']);
		$label = trim($recipeName) !== '' ? trim($recipeName) : self::FALLBACK_LABEL;
		$rows = [];

		foreach ($lines as $position => $line) {
			$parsed = $this->parser->parse($line['line']);
			if ($parsed['kind'] !== 'item') {
				continue;
			}

			$name = $parsed['name'];
			$requested = $line['name'] ?? null;
			if ($requested !== null && in_array($requested, $parsed['alternatives'], true)) {
				$name = $requested;
			}

			$enabled = $line['enabled'] ?? !$parsed['isStaple'];

			$row = [
				'index' => $position,
				'original' => $parsed['original'],
				'name' => $name,
				'alternatives' => $parsed['alternatives'],
				'isStaple' => $parsed['isStaple'],
				'enabled' => $enabled,
				'action' => self::ACTION_SKIPPED,
				'itemId' => $name,
				'uuid' => '',
				'existingSpec' => null,
				'resultSpec' => $parsed['spec'],
			];

			if ($enabled) {
				$decision = $mode === self::MODE_SEPARATE
					? $this->placeSeparate($name, $parsed, $label, $purchase, $recently)
					: $this->placeMerged($name, $parsed, $label, $purchase, $recently);
				$row = array_merge($row, $decision);

				if ($this->isWrite($row['action'])) {
					$key = $this->key($row['itemId']);
					$purchase[$key] = [
						'uuid' => $row['uuid'],
						'itemId' => $row['itemId'],
						'spec' => $row['resultSpec'],
					];
					unset($recently[$key]);
				}
			}

			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Whether the action changes the Bring! list.
	 */
	public function isWrite(string $action): bool {
		return in_array($action, [self::ACTION_ADD, self::ACTION_READD, self::ACTION_SUM, self::ACTION_UPDATE], true);
	}

	/**
	 * @param array<string, mixed> $parsed result of BringItemParser::parse()
	 * @param array<string, array{uuid: string, itemId: string, spec: string}> $purchase
	 * @param array<string, array{uuid: string, itemId: string, spec: string}> $recently
	 * @return array{action: string, itemId: string, uuid: string, existingSpec: string|null, resultSpec: string}
	 */
	private function placeMerged(string $name, array $parsed, string $label, array $purchase, array $recently): array {
		$key = $this->key($name);
		$existing = $purchase[$key] ?? null;

		if ($existing !== null) {
			if ($existing['spec'] === '' && $parsed['spec'] === '') {
				return $this->decision(self::ACTION_UNCHANGED, $existing['itemId'], $existing['uuid'], $existing['spec'], $existing['spec']);
			}

			$sum = $this->trySum($existing['spec'], $parsed);
			if ($sum !== null) {
				return $this->decision(self::ACTION_SUM, $existing['itemId'], $existing['uuid'], $existing['spec'], $sum);
			}

			return $this->placeSeparate($name, $parsed, $label, $purchase, $recently);
		}

		$back = $recently[$key] ?? null;
		if ($back !== null) {
			return $this->decision(self::ACTION_READD, $back['itemId'], $back['uuid'], null, $parsed['spec']);
		}

		return $this->decision(self::ACTION_ADD, $name, '', null, $parsed['spec']);
	}

	/**
	 * @param array<string, mixed> $parsed result of BringItemParser::parse()
	 * @param array<string, array{uuid: string, itemId: string, spec: string}> $purchase
	 * @param array<string, array{uuid: string, itemId: string, spec: string}> $recently
	 * @return array{action: string, itemId: string, uuid: string, existingSpec: string|null, resultSpec: string}
	 */
	private function placeSeparate(string $name, array $parsed, string $label, array $purchase, array $recently): array {
		$itemId = $name . ' (' . $label . ')';
		$key = $this->key($itemId);

		$existing = $purchase[$key] ?? null;
		if ($existing !== null) {
			$action = $existing['spec'] === $parsed['spec'] ? self::ACTION_UNCHANGED : self::ACTION_UPDATE;

			return $this->decision($action, $existing['itemId'], $existing['uuid'], $existing['spec'], $parsed['spec']);
		}

		$back = $recently[$key] ?? null;
		if ($back !== null) {
			return $this->decision(self::ACTION_READD, $back['itemId'], $back['uuid'], null, $parsed['spec']);
		}

		return $this->decision(self::ACTION_ADD, $itemId, '', null, $parsed['spec']);
	}

	/**
	 * Add the amount of the parsed line to the spec of an existing entry.
	 *
	 * The notes of both entries are kept, existing notes first, duplicates removed.
	 *
	 * @param array<string, mixed> $parsed result of BringItemParser::parse()
	 * @return string|null the new spec, or null if the two cannot be summed
	 */
	private function trySum(string $existingSpec, array $parsed): ?string {
		if ($parsed['amount'] === null || $parsed['quantityText'] === '') {
			return null;
		}

		$existing = $this->parser->parseQuantity($existingSpec);
		if ($existing === null || $existing['amount'] === null) {
			return null;
		}

		if ($this->comparableUnit($existing['unit']) !== $this->comparableUnit($parsed['unit'])) {
			return null;
		}

		$unitText = $existing['unitText'] !== '' ? $existing['unitText'] : $parsed['unitText'];
		$total = $this->formatAmount($existing['amount'] + $parsed['amount']);
		$notes = array_values(array_unique(array_merge($existing['notes'], $parsed['notes'])));

		$spec = trim($total . ($unitText !== '' ? ' ' . $unitText : ''));
		if ($notes !== []) {
			$spec .= ' ' . implode(' ', $notes);
		}

		return $spec;
	}

	private function comparableUnit(string $unit): string {
		return $unit === '' ? self::PIECE_UNIT : $unit;
	}

	private function formatAmount(float $value): string {
		$text = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

		return $text === '' ? '0' : $text;
	}

	/**
	 * @return array{action: string, itemId: string, uuid: string, existingSpec: string|null, resultSpec: string}
	 */
	private function decision(string $action, string $itemId, string $uuid, ?string $existingSpec, string $resultSpec): array {
		return [
			'action' => $action,
			'itemId' => $itemId,
			'uuid' => $uuid,
			'existingSpec' => $existingSpec,
			'resultSpec' => $resultSpec,
		];
	}

	/**
	 * Index entries by lower-case name. The first entry wins if a name occurs twice.
	 *
	 * @param list<array{uuid: string, itemId: string, spec: string}> $entries
	 * @return array<string, array{uuid: string, itemId: string, spec: string}>
	 */
	private function index(array $entries): array {
		$index = [];
		foreach ($entries as $entry) {
			$key = $this->key($entry['itemId']);
			if (!isset($index[$key])) {
				$index[$key] = $entry;
			}
		}

		return $index;
	}

	private function key(string $itemId): string {
		return mb_strtolower(trim($itemId));
	}
}
