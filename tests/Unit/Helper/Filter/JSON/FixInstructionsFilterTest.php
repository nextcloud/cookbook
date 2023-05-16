<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCA\Cookbook\Helper\Filter\JSON\FixInstructionsFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCA\Cookbook\Service\JsonService;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers OCA\Cookbook\Helper\Filter\JSON\FixInstructionsFilter
 * @covers OCA\Cookbook\Exception\InvalidRecipeException
 */
class FixInstructionsFilterTest extends TestCase {
	/** @var FixInstructionsFilter */
	private $dut;

	/** @var TextCleanupHelper|Stub */
	private $textCleanupHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		/** @var LoggerInterface */
		$logger = $this->createStub(LoggerInterface::class);

		$this->textCleanupHelper = $this->createStub(TextCleanupHelper::class);
		$jsonService = new JsonService();

		$this->dut = new FixInstructionsFilter($l, $logger, $this->textCleanupHelper, $jsonService);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;
		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['recipeInstructions'] = [];
		$this->assertEquals($this->stub, $recipe);
	}

	public function dpBadInstructions() {
		return [
			[null],
			[''],
		];
	}

	/** @dataProvider dpBadInstructions */
	public function testBadInstructions($value) {
		$recipe = $this->stub;
		$recipe['recipeInstructions'] = $value;

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['recipeInstructions'] = [];
		$this->assertEquals($this->stub, $recipe);
	}

	public function testInvalidInstructions() {
		$recipe = $this->stub;
		$recipe['recipeInstructions'] = 123;
		$this->expectException(InvalidRecipeException::class);
		$this->dut->apply($recipe);
	}

	public function dpParseInstructions() {
		yield 'plain strings' => [['a', 'b', 'c'], ['a','b','c'], false];
		yield 'Single ItemList with strings' => [[
			'@type' => 'ItemList',
			'itemListElement' => ['a', 'b', 'c'],
		], ['a', 'b', 'c'], true];
		yield 'Single ItemList with list items' => [[
			'@type' => 'ItemList',
			'itemListElement' => [
				[ '@type' => 'ListItem', 'item' => 'a', ],
				[ '@type' => 'ListItem', 'item' => 'b', ],
				[ '@type' => 'ListItem', 'item' => 'c', ],
			],
		], ['a', 'b', 'c'], true];
		yield 'Single ItemList with list items and position' => [[
			'@type' => 'ItemList',
			'itemListElement' => [
				[ '@type' => 'ListItem', 'item' => 'a', 'position' => 3, ],
				[ '@type' => 'ListItem', 'item' => 'b', 'position' => 1, ],
				[ '@type' => 'ListItem', 'item' => 'c', 'position' => 2, ],
			],
		], ['b', 'c', 'a'], true];
		yield 'Nested ItemList in array' => [[
			[
				'@type' => 'ItemList',
				'itemListElement' => ['a','b'],
			],
			[
				'@type' => 'ItemList',
				'itemListElement' => ['c','d'],
			],
		], ['a', 'b', 'c', 'd'], true];
		yield 'Nested ItemList in array recursively' => [[
			[
				'@type' => 'ItemList',
				'itemListElement' => [
					[
						'@type' => 'ListItem',
						'item' => [
							'@type' => 'ItemList',
							'itemListElement' => ['a', 'b']
						]
					],
					'c',
				],
			], 'd',
		], ['a', 'b', 'c', 'd'], true];

		yield 'Instructions in multiple lines' => [
			"a\nb\rc\r\nd",
			['a','b','c','d'], true
		];
		yield 'Instructions with HTML Entities' => [
			"<p>a</p>\n<ul><li>b</li><li>c</li></ul><p>d</p>",
			['a','b','c','d'], true
		];
		yield 'Instructions with empty HTML Entities' => [
			"a\n<p></p><ul><li></li><li></li></ul><p></p>\nb\nc\nd",
			['a','b','c','d'], true
		];

		yield 'Instructions with Markdown' => [
			["a\nb\nc"],
			["a\nb\nc"], false
		];

		yield 'Array of HoToSteps' => [
			[
				[
					'@type' => 'HowToStep',
					'text' => 'a',
				],
				[
					'@type' => 'HowToStep',
					'text' => 'b',
				],
				[
					'@type' => 'HowToStep',
					'text' => 'c',
				],
			], ['a', 'b', 'c'], true
		];

		yield 'HowToSections' => [
			[
				[
					'@type' => 'HowToSection',
					'name' => 'Foo',
					'itemListElement' => [
						[
							'@type' => 'HowToStep',
							'text' => 'a',
						],
						[
							'@type' => 'HowToStep',
							'text' => 'b',
						],
					],
				],
				[
					'@type' => 'HowToSection',
					'itemListElement' => [
						[
							'@type' => 'HowToStep',
							'text' => 'c',
						],
						[
							'@type' => 'HowToStep',
							'text' => 'd',
						],
					],
				],
				[
					'@type' => 'HowToStep',
					'text' => 'e',
				],
			], ['## Foo', 'a', 'b', '## HowToSection', 'c', 'd', 'e'], true
		];

		yield 'Issue1210' => [
			['a', '', 'b'], ['a', 'b'], true
		];
	}

	/** @dataProvider dpParseInstructions */
	public function testParseInstructions($originalInstructions, $expected, $changeExpected) {
		$recipe = $this->stub;
		$recipe['recipeInstructions'] = $originalInstructions;

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);

		$changed = $this->dut->apply($recipe);

		$this->stub['recipeInstructions'] = $expected;
		$this->assertEquals($this->stub, $recipe);
		$this->assertEquals($changeExpected, $changed);
	}

	public function dpUnknownType() {
		yield 'List of Things' => [[
			[
				'@type' => 'Thing',
			],
			[
				'@type' => 'Thing',
			],
		]];
		yield 'ItemList with Thing' => [[
			'@type' => 'ItemList',
			'itemListElement' => [
				'a',
				[
					'@type' => 'Thing',
				],
			],
		]];
		yield 'ItemList with Thing in ListItem' => [[
			'@type' => 'ItemList',
			'itemListElement' => [
				'a',
				[
					'@type' => 'ListItem',
					'item' => [
						'b',
						[
							'@type' => 'Thing',
						],
					],
				],
			],
		]];
	}

	/** @dataProvider dpUnknownType */
	public function testUnknownType($originalInstructions) {
		$recipe = $this->stub;
		$recipe['recipeInstructions'] = $originalInstructions;

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);
		$this->expectException(InvalidRecipeException::class);

		$this->dut->apply($recipe);
	}

	public function dpRealWorldIssues() {
		return [
			'case01' => ['case01.json', 'case01.expected.json', true],
		];
	}

	/** @dataProvider dpRealWorldIssues */
	public function testRealWorldIssue($fileName, $expectedFileName, $shouldChange) {
		$originalRaw = file_get_contents(__DIR__ . "/res_FixInstructionsFilter/$fileName");
		$expectedRaw = file_get_contents(__DIR__ . "/res_FixInstructionsFilter/$expectedFileName");

		$original = json_decode($originalRaw, true);
		$expected = json_decode($expectedRaw, true);

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);

		$ret = $this->dut->apply($original);

		$this->assertEquals($expected, $original);
		$this->assertEquals($shouldChange, $ret);
	}
}
