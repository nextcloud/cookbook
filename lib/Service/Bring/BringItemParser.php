<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service\Bring;

/**
 * Splits an ingredient line into the parts needed for a Bring! list entry.
 *
 * The lines are already scaled by Cookbook. This class never changes an amount,
 * it only reads the line. Bring! identifies an entry by its name (`itemId`) and
 * stores the quantity as free text (`spec`).
 */
class BringItemParser {
	/**
	 * Words that count as a unit when they directly follow the amount.
	 * Anything else after the amount belongs to the ingredient name.
	 */
	private const UNITS = [
		// Metric
		'kg', 'g', 'gr', 'mg', 'l', 'ml', 'cl', 'dl', 'liter', 'liters', 'litre', 'litres',
		// German
		'tl', 'el', 'prise', 'prisen', 'msp', 'messerspitze', 'messerspitzen', 'bund', 'bd',
		'dose', 'dosen', 'pck', 'pkg', 'päckchen', 'packung', 'packungen', 'becher', 'glas',
		'gläser', 'scheibe', 'scheiben', 'stück', 'stk', 'zehe', 'zehen', 'handvoll', 'schuss',
		'spritzer', 'tasse', 'tassen', 'kopf', 'köpfe', 'stange', 'stangen', 'blatt', 'blätter',
		'zweig', 'zweige', 'stiel', 'stiele', 'würfel', 'flasche', 'flaschen',
		// English
		'tsp', 'tbsp', 'teaspoon', 'teaspoons', 'tablespoon', 'tablespoons', 'cup', 'cups',
		'oz', 'lb', 'lbs', 'pinch', 'pinches', 'dash', 'can', 'cans', 'clove', 'cloves',
		'slice', 'slices', 'piece', 'pieces', 'bunch', 'bunches', 'package', 'packages',
		'handful', 'sprig', 'sprigs', 'stick', 'sticks', 'head', 'heads', 'bottle', 'bottles',
		'jar', 'jars',
	];

	/**
	 * Spellings that mean the same unit, mapped to one key so amounts can be summed.
	 */
	private const UNIT_ALIASES = [
		'stk' => 'stück',
		'gr' => 'g',
		'liter' => 'l',
		'liters' => 'l',
		'litre' => 'l',
		'litres' => 'l',
		'bd' => 'bund',
		'prisen' => 'prise',
		'messerspitzen' => 'messerspitze',
		'dosen' => 'dose',
		'pck' => 'packung',
		'pkg' => 'packung',
		'packungen' => 'packung',
		'gläser' => 'glas',
		'scheiben' => 'scheibe',
		'zehen' => 'zehe',
		'tassen' => 'tasse',
		'köpfe' => 'kopf',
		'stangen' => 'stange',
		'blätter' => 'blatt',
		'zweige' => 'zweig',
		'stiele' => 'stiel',
		'flaschen' => 'flasche',
		'teaspoon' => 'tsp',
		'teaspoons' => 'tsp',
		'tablespoon' => 'tbsp',
		'tablespoons' => 'tbsp',
		'cups' => 'cup',
		'lbs' => 'lb',
		'pinches' => 'pinch',
		'cans' => 'can',
		'cloves' => 'clove',
		'slices' => 'slice',
		'pieces' => 'piece',
		'bunches' => 'bunch',
		'packages' => 'package',
		'sprigs' => 'sprig',
		'sticks' => 'stick',
		'heads' => 'head',
		'bottles' => 'bottle',
		'jars' => 'jar',
	];

	/**
	 * Basic ingredients. Without an amount they are unchecked by default in the preview.
	 */
	private const STAPLES = [
		'wasser', 'salz', 'pfeffer', 'zucker', 'mehl', 'öl', 'olivenöl', 'butter', 'milch',
		'brühe', 'gemüsebrühe', 'salz und pfeffer', 'salz & pfeffer',
		'water', 'salt', 'pepper', 'sugar', 'flour', 'oil', 'olive oil', 'milk', 'broth', 'stock',
		'salt and pepper',
	];

	/**
	 * Phrases ignored when checking whether a name is a staple.
	 */
	private const STAPLE_NOISE = ['nach geschmack', 'nach belieben', 'to taste', 'optional'];

	private const UNICODE_FRACTIONS = [
		'¼' => 1 / 4,
		'½' => 1 / 2,
		'¾' => 3 / 4,
		'⅐' => 1 / 7,
		'⅑' => 1 / 9,
		'⅒' => 1 / 10,
		'⅓' => 1 / 3,
		'⅔' => 2 / 3,
		'⅕' => 1 / 5,
		'⅖' => 2 / 5,
		'⅗' => 3 / 5,
		'⅘' => 4 / 5,
		'⅙' => 1 / 6,
		'⅚' => 5 / 6,
		'⅛' => 1 / 8,
		'⅜' => 3 / 8,
		'⅝' => 5 / 8,
		'⅞' => 7 / 8,
	];

	private const FRACTION_CHARS = '[¼½¾\x{2150}-\x{215E}]';

	/**
	 * One amount: mixed number, fraction, whole number with unicode fraction, unicode fraction or decimal.
	 * Decimals accept a comma or a dot.
	 */
	private const NUMBER = '(?:\d+\s+\d+\s*/\s*\d+|\d+\s*/\s*\d+|\d+\s*' . self::FRACTION_CHARS . '|' . self::FRACTION_CHARS . '|\d+(?:[.,]\d+)?)';

	/**
	 * Parse one ingredient line.
	 *
	 * `kind` is `item`, `header` (a "## " section line) or `empty`; only `item` is exported.
	 * `amount` is null if there is no amount or it is a range, so such lines are never summed.
	 * `unit` is the normalized unit key, `unitText` the unit as written, both empty if there is none.
	 * `spec` is the quantity text followed by the notes, as it is sent to Bring!.
	 *
	 * @return array{
	 *     original: string,
	 *     kind: string,
	 *     name: string,
	 *     alternatives: list<string>,
	 *     quantityText: string,
	 *     amount: float|null,
	 *     unit: string,
	 *     unitText: string,
	 *     notes: list<string>,
	 *     spec: string,
	 *     isStaple: bool
	 * }
	 */
	public function parse(string $line): array {
		$original = trim($line);

		$result = [
			'original' => $original,
			'kind' => 'item',
			'name' => $original,
			'alternatives' => [],
			'quantityText' => '',
			'amount' => null,
			'unit' => '',
			'unitText' => '',
			'notes' => [],
			'spec' => '',
			'isStaple' => false,
		];

		if ($original === '') {
			$result['kind'] = 'empty';

			return $result;
		}

		if (str_starts_with($original, '## ')) {
			$result['kind'] = 'header';

			return $result;
		}

		$quantityText = '';
		$amount = null;
		$unit = '';
		$unitText = '';
		$remainder = $original;

		if (preg_match($this->quantityPattern(), $original, $matches) === 1 && trim($matches['rest']) !== '') {
			$quantity = $this->readQuantity($matches);
			$quantityText = $quantity['quantityText'];
			$amount = $quantity['amount'];
			$unit = $quantity['unit'];
			$unitText = $quantity['unitText'];
			$remainder = trim($matches['rest']);
		}

		[$remainder, $notes] = $this->extractNotes($remainder);
		$remainder = $this->clean($remainder);

		if ($remainder === '') {
			return $result;
		}

		$alternatives = [];
		$parts = preg_split('~\s+(?:/|oder|or)\s+~iu', $remainder);
		if ($parts !== false && count($parts) >= 2) {
			$cleaned = array_map(fn (string $part): string => $this->clean($part), $parts);
			if (!in_array('', $cleaned, true)) {
				$alternatives = $cleaned;
			}
		}

		$name = $alternatives !== [] ? $alternatives[0] : $remainder;

		$result['name'] = $name;
		$result['alternatives'] = $alternatives;
		$result['quantityText'] = $quantityText;
		$result['amount'] = $amount;
		$result['unit'] = $unit;
		$result['unitText'] = $unitText;
		$result['notes'] = $notes;
		$result['spec'] = trim($quantityText . ' ' . implode(' ', $notes));
		$result['isStaple'] = $quantityText === '' && $alternatives === [] && $this->isStaple($name);

		return $result;
	}

	/**
	 * Read a text that consists of a quantity plus optional notes, such as the spec of an existing list entry.
	 *
	 * @return array{quantityText: string, amount: float|null, unit: string, unitText: string, notes: list<string>}
	 *                                                                                                              null if the text contains no quantity (for example "ca. 500 g" or a bare note like "(Bio)")
	 */
	public function parseQuantity(string $text): ?array {
		$text = trim($text);
		[$text, $notes] = $this->extractNotes($text);
		$text = $this->clean($text);
		if (preg_match($this->quantityPattern(), $text, $matches) !== 1 || trim($matches['rest']) !== '') {
			return null;
		}

		$quantity = $this->readQuantity($matches);
		$quantity['notes'] = $notes;

		return $quantity;
	}

	/**
	 * Remove all parenthesized notes from the text.
	 *
	 * @return array{0: string, 1: list<string>} the note-free text and the notes, in order of appearance
	 */
	private function extractNotes(string $text): array {
		$notes = [];
		$cleaned = preg_replace_callback(
			'~\([^)]*\)~u',
			function (array $match) use (&$notes): string {
				$notes[] = $match[0];

				return ' ';
			},
			$text,
		);

		return [$cleaned ?? $text, $notes];
	}

	/**
	 * @param array<int|string, string> $matches
	 * @return array{quantityText: string, amount: float|null, unit: string, unitText: string}
	 */
	private function readQuantity(array $matches): array {
		$amountText = preg_replace('~\s+~u', ' ', $matches['amount']) ?? $matches['amount'];
		$unitText = $matches['unit'] ?? '';
		$unitKey = mb_strtolower($unitText);

		return [
			'quantityText' => $amountText . ($unitText !== '' ? ' ' . $unitText : ''),
			'amount' => $this->computeAmount($amountText),
			'unit' => self::UNIT_ALIASES[$unitKey] ?? $unitKey,
			'unitText' => $unitText,
		];
	}

	private function quantityPattern(): string {
		return '~^(?P<amount>' . self::NUMBER . '(?:\s*[-–]\s*' . self::NUMBER . ')?)(?![.,]?\d)'
			. '(?:\s*(?P<unit>' . $this->unitAlternation() . ')\.?(?=$|[\s,;:()/]))?'
			. '(?P<rest>.*)$~iu';
	}

	private function unitAlternation(): string {
		static $alternation = null;

		if ($alternation === null) {
			$units = self::UNITS;
			usort($units, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));
			$alternation = implode('|', array_map(fn (string $unit): string => preg_quote($unit, '~'), $units));
		}

		return $alternation;
	}

	/**
	 * Numeric value of an amount, or null for ranges and unreadable amounts.
	 */
	private function computeAmount(string $amountText): ?float {
		$range = '~^' . self::NUMBER . '\s*[-–]\s*' . self::NUMBER . '$~u';
		if (preg_match($range, $amountText) === 1) {
			return null;
		}

		return $this->parseNumber($amountText);
	}

	private function parseNumber(string $text): ?float {
		$text = trim($text);

		if (preg_match('~^(\d+)\s+(\d+)\s*/\s*(\d+)$~u', $text, $m) === 1) {
			return $this->fraction((int)$m[2], (int)$m[3], (int)$m[1]);
		}

		if (preg_match('~^(\d+)\s*/\s*(\d+)$~u', $text, $m) === 1) {
			return $this->fraction((int)$m[1], (int)$m[2], 0);
		}

		if (preg_match('~^(\d*)\s*(' . self::FRACTION_CHARS . ')$~u', $text, $m) === 1) {
			$fraction = self::UNICODE_FRACTIONS[$m[2]] ?? null;
			$whole = (float)($m[1] === '' ? 0 : $m[1]);

			return $fraction === null ? null : $whole + (float)$fraction;
		}

		if (preg_match('~^\d+(?:[.,]\d+)?$~', $text) === 1) {
			return (float)str_replace(',', '.', $text);
		}

		return null;
	}

	private function fraction(int $numerator, int $denominator, int $whole): ?float {
		if ($denominator === 0) {
			return null;
		}

		return (float)$whole + ((float)$numerator / (float)$denominator);
	}

	private function isStaple(string $name): bool {
		$normalized = mb_strtolower($name);
		$normalized = str_replace(self::STAPLE_NOISE, '', $normalized);
		$normalized = trim(preg_replace('~\s+~u', ' ', $normalized) ?? '');

		return in_array($normalized, self::STAPLES, true);
	}

	/**
	 * Collapse whitespace and remove dangling separators at both ends.
	 */
	private function clean(string $text): string {
		$text = preg_replace('~\s+~u', ' ', $text) ?? $text;

		return preg_replace('~^[\s,;:\-–]+|[\s,;:\-–]+$~u', '', $text) ?? $text;
	}
}
