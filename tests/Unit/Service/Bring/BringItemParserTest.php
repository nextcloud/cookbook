<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace OCA\Cookbook\tests\Unit\Service\Bring;

use OCA\Cookbook\Service\Bring\BringItemParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BringItemParserTest extends TestCase {
	/** @var BringItemParser */
	private $parser;

	protected function setUp(): void {
		parent::setUp();
		$this->parser = new BringItemParser();
	}

	#[DataProvider('provideLines')]
	public function testParse(string $input, array $expected): void {
		$result = $this->parser->parse($input);

		$keys = ['kind', 'name', 'quantityText', 'amount', 'unit', 'notes', 'spec', 'isStaple', 'alternatives'];
		foreach ($keys as $key) {
			if (array_key_exists($key, $expected)) {
				$this->assertArrayHasKey($key, $result, "Missing key '{$key}' for input: " . json_encode($input, JSON_UNESCAPED_UNICODE));
				$this->assertSame($expected[$key], $result[$key], "Mismatch on key '{$key}' for input: " . json_encode($input, JSON_UNESCAPED_UNICODE));
			}
		}
	}

	public static function provideLines(): array {
		return [
			'item with notes' => [
				'1 kg Schweinebauch (mit Schwarte, möglichst ohne Knochen)',
				[
					'kind' => 'item',
					'name' => 'Schweinebauch',
					'quantityText' => '1 kg',
					'amount' => 1.0,
					'unit' => 'kg',
					'notes' => ['(mit Schwarte, möglichst ohne Knochen)'],
					'spec' => '1 kg (mit Schwarte, möglichst ohne Knochen)',
					'isStaple' => false,
					'alternatives' => [],
				],
			],
			'teaspoon unit' => [
				'4 TL grobes Meersalz',
				[
					'name' => 'grobes Meersalz',
					'quantityText' => '4 TL',
					'amount' => 4.0,
					'unit' => 'tl',
					'spec' => '4 TL',
				],
			],
			'alternatives with slash' => [
				'100 ml Weißwein / Bier / Rotwein',
				[
					'name' => 'Weißwein',
					'alternatives' => ['Weißwein', 'Bier', 'Rotwein'],
					'quantityText' => '100 ml',
					'amount' => 100.0,
					'unit' => 'ml',
					'spec' => '100 ml',
				],
			],
			'count without unit' => [
				'2 Lorbeerblätter',
				[
					'name' => 'Lorbeerblätter',
					'quantityText' => '2',
					'amount' => 2.0,
					'unit' => '',
					'spec' => '2',
				],
			],
			'teaspoon with adjective' => [
				'1 TL schwarze Pfefferkörner',
				[
					'name' => 'schwarze Pfefferkörner',
					'quantityText' => '1 TL',
					'amount' => 1.0,
					'unit' => 'tl',
				],
			],
			'fraction quantity' => [
				'1/2 TL Wacholderbeeren',
				[
					'name' => 'Wacholderbeeren',
					'quantityText' => '1/2 TL',
					'amount' => 0.5,
					'unit' => 'tl',
				],
			],
			'count garlic cloves' => [
				'3 Knoblauchzehen',
				[
					'name' => 'Knoblauchzehen',
					'quantityText' => '3',
					'amount' => 3.0,
					'unit' => '',
				],
			],
			'staple water' => [
				'Wasser',
				[
					'name' => 'Wasser',
					'quantityText' => '',
					'amount' => null,
					'spec' => '',
					'isStaple' => true,
				],
			],
			'header line' => [
				'## Für die Soße',
				[
					'kind' => 'header',
				],
			],
			'decimal with comma' => [
				'1,5 l Milch',
				[
					'name' => 'Milch',
					'quantityText' => '1,5 l',
					'amount' => 1.5,
					'unit' => 'l',
					'isStaple' => false,
				],
			],
			'range quantity' => [
				'2-3 Zwiebeln',
				[
					'name' => 'Zwiebeln',
					'quantityText' => '2-3',
					'amount' => null,
					'unit' => '',
					'spec' => '2-3',
				],
			],
			'unicode fraction' => [
				'1½ EL Honig',
				[
					'name' => 'Honig',
					'quantityText' => '1½ EL',
					'amount' => 1.5,
					'unit' => 'el',
				],
			],
			'mixed fraction with plural' => [
				'1 1/2 Tassen Mehl',
				[
					'name' => 'Mehl',
					'quantityText' => '1 1/2 Tassen',
					'amount' => 1.5,
					'unit' => 'tasse',
					'isStaple' => false,
				],
			],
			'staple salt' => [
				'Salz',
				[
					'name' => 'Salz',
					'isStaple' => true,
				],
			],
			'pinch quantity' => [
				'1 Prise Salz',
				[
					'name' => 'Salz',
					'quantityText' => '1 Prise',
					'amount' => 1.0,
					'unit' => 'prise',
					'isStaple' => false,
				],
			],
			'staple with note' => [
				'Wasser (kalt)',
				[
					'name' => 'Wasser',
					'notes' => ['(kalt)'],
					'spec' => '(kalt)',
					'isStaple' => true,
				],
			],
			'no space before unit' => [
				'200g Mehl',
				[
					'name' => 'Mehl',
					'quantityText' => '200 g',
					'amount' => 200.0,
					'unit' => 'g',
				],
			],
			'empty string' => [
				'',
				[
					'kind' => 'empty',
				],
			],
			'single item' => [
				'1 Zwiebel',
				[
					'name' => 'Zwiebel',
					'quantityText' => '1',
					'amount' => 1.0,
					'unit' => '',
				],
			],
			'alternatives with oder' => [
				'Zucker oder Honig',
				[
					'name' => 'Zucker',
					'alternatives' => ['Zucker', 'Honig'],
					'quantityText' => '',
					'isStaple' => false,
				],
			],
			'decimal with dot' => [
				'1.67 kg Hackfleisch',
				[
					'name' => 'Hackfleisch',
					'quantityText' => '1.67 kg',
					'amount' => 1.67,
					'unit' => 'kg',
				],
			],
			'abbreviated piece unit' => [
				'2 Stk. Zwiebel',
				[
					'name' => 'Zwiebel',
					'quantityText' => '2 Stk',
					'amount' => 2.0,
					'unit' => 'stück',
				],
			],
			'tablespoon oil' => [
				'1 EL Öl',
				[
					'name' => 'Öl',
					'quantityText' => '1 EL',
					'amount' => 1.0,
					'unit' => 'el',
					'isStaple' => false,
				],
			],
			'quantity only no name' => [
				'200 g',
				[
					'name' => '200 g',
					'quantityText' => '',
					'amount' => null,
					'kind' => 'item',
				],
			],
		];
	}

	public function testParseQuantity(): void {
		// '2 Stück'
		$result = $this->parser->parseQuantity('2 Stück');
		$this->assertNotNull($result);
		$this->assertSame('2 Stück', $result['quantityText']);
		$this->assertSame(2.0, $result['amount']);
		$this->assertSame('stück', $result['unit']);
		$this->assertSame('Stück', $result['unitText']);
		$this->assertSame([], $result['notes']);

		// '200 g'
		$result = $this->parser->parseQuantity('200 g');
		$this->assertNotNull($result);
		$this->assertSame(200.0, $result['amount']);
		$this->assertSame('g', $result['unit']);

		// '1,5 l'
		$result = $this->parser->parseQuantity('1,5 l');
		$this->assertNotNull($result);
		$this->assertSame(1.5, $result['amount']);
		$this->assertSame('l', $result['unit']);

		// 'ca. 500g' -> null (approximate prefix not supported)
		$result = $this->parser->parseQuantity('ca. 500g');
		$this->assertNull($result);

		// '' -> null
		$result = $this->parser->parseQuantity('');
		$this->assertNull($result);

		// '(Bio)' -> null (bare note, no quantity)
		$result = $this->parser->parseQuantity('(Bio)');
		$this->assertNull($result);

		// '2 (Bio)' -> quantity with a note
		$result = $this->parser->parseQuantity('2 (Bio)');
		$this->assertNotNull($result);
		$this->assertSame('2', $result['quantityText']);
		$this->assertSame(2.0, $result['amount']);
		$this->assertSame('', $result['unit']);
		$this->assertSame(['(Bio)'], $result['notes']);

		// '1 kg (mit Schwarte)' -> quantity with a note
		$result = $this->parser->parseQuantity('1 kg (mit Schwarte)');
		$this->assertNotNull($result);
		$this->assertSame('1 kg', $result['quantityText']);
		$this->assertSame(1.0, $result['amount']);
		$this->assertSame('kg', $result['unit']);
		$this->assertSame(['(mit Schwarte)'], $result['notes']);

		// '2-3 Zwiebeln' -> null (range not a single quantity)
		$result = $this->parser->parseQuantity('2-3 Zwiebeln');
		$this->assertNull($result);
	}

	public function testHeaderAndEmpty(): void {
		// Header
		$result = $this->parser->parse('## Für die Soße');
		$this->assertSame('header', $result['kind']);

		// Empty string
		$result = $this->parser->parse('');
		$this->assertSame('empty', $result['kind']);

		// Whitespace only
		$result = $this->parser->parse('   ');
		$this->assertSame('empty', $result['kind']);
	}

	public function testStaples(): void {
		// Water is a staple
		$result = $this->parser->parse('Wasser');
		$this->assertTrue($result['isStaple']);

		// Salt is a staple
		$result = $this->parser->parse('Salz');
		$this->assertTrue($result['isStaple']);

		// Pepper is a staple
		$result = $this->parser->parse('Pfeffer');
		$this->assertTrue($result['isStaple']);

		// Water with note is still a staple
		$result = $this->parser->parse('Wasser (kalt)');
		$this->assertTrue($result['isStaple']);

		// Salt with quantity is NOT a staple
		$result = $this->parser->parse('1 Prise Salz');
		$this->assertFalse($result['isStaple']);

		// Item with alternatives is NOT a staple
		$result = $this->parser->parse('Zucker oder Honig');
		$this->assertFalse($result['isStaple']);

		// Regular ingredient is NOT a staple
		$result = $this->parser->parse('Schweinebauch');
		$this->assertFalse($result['isStaple']);
	}
}
