<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Exception\InvalidAssistantResponseException;
use OCA\Cookbook\Exception\InvalidRecipeException;
use OCA\Cookbook\Helper\AssistantRecipeHelper;
use OCA\Cookbook\Helper\Filter\JSON\JSONFilter;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\AssistantRecipeHelper
 */
class AssistantRecipeHelperTest extends TestCase {
	private const DEFAULTS = [
		'name' => '',
		'description' => '',
		'recipeCategory' => '',
		'keywords' => '',
		'prepTime' => '',
		'cookTime' => '',
		'totalTime' => '',
		'recipeYield' => null,
		'tool' => [],
		'recipeIngredient' => [],
		'recipeInstructions' => [],
		'nutrition' => [],
	];

	/** @var AssistantRecipeHelper */
	private $dut;
	/** @var JSONFilter|MockObject */
	private $jsonFilter;

	public function setUp(): void {
		parent::setUp();

		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$this->jsonFilter = $this->createMock(JSONFilter::class);

		$this->dut = new AssistantRecipeHelper($l, $this->jsonFilter);
	}

	public function testExtractEditableFields(): void {
		$recipe = [
			'id' => 12,
			'name' => 'Pancakes',
			'description' => 'Fluffy',
			'url' => 'https://example.com',
			'image' => 'https://example.com/image.jpg',
			'prepTime' => 'PT0H10M',
			'cookTime' => '',
			'totalTime' => 'PT0H30M',
			'recipeCategory' => 'Breakfast',
			'keywords' => 'sweet,quick',
			'recipeYield' => 4,
			'tool' => ['Pan'],
			'recipeIngredient' => ['1 cup flour', '1 egg'],
			'recipeInstructions' => ['Mix', 'Fry'],
			'nutrition' => ['calories' => '450 kcal'],
			'dateCreated' => '2024-01-01',
		];

		$expected = $recipe;
		unset($expected['id'], $expected['url'], $expected['image'], $expected['dateCreated']);

		$this->assertEquals($expected, $this->dut->extractEditableFields($recipe));
	}

	public function testExtractEditableFieldsDefaults(): void {
		$this->assertEquals(self::DEFAULTS, $this->dut->extractEditableFields(['id' => 1]));
		$this->assertEquals(self::DEFAULTS, $this->dut->extractEditableFields(['recipeYield' => '']));
	}

	public function testBuildPrompt(): void {
		$recipe = [
			'id' => 12,
			'name' => 'Pancakes',
			'recipeIngredient' => ['1 cup flour'],
		];

		$prompt = $this->dut->buildPrompt($recipe, 'Translate the recipe to German');

		$this->assertStringContainsString('Translate the recipe to German', $prompt);
		$this->assertStringContainsString('"name": "Pancakes"', $prompt);
		$this->assertStringContainsString('"1 cup flour"', $prompt);
		$this->assertStringNotContainsString('"id"', $prompt);
	}

	/**
	 * @dataProvider dpParseResponse
	 */
	public function testParseResponse(string $output, array $expectedFiltered, array $expected): void {
		$this->jsonFilter->expects($this->once())->method('apply')->with($expectedFiltered)->willReturnArgument(0);

		$this->assertEquals($expected, $this->dut->parseResponse($output));
	}

	public static function dpParseResponse(): array {
		$full = [
			'name' => 'Pfannkuchen',
			'description' => 'Fluffig',
			'recipeCategory' => 'Frühstück',
			'keywords' => 'süß,schnell',
			'prepTime' => 'PT0H10M',
			'cookTime' => '',
			'totalTime' => 'PT0H30M',
			'recipeYield' => 4,
			'tool' => ['Pfanne'],
			'recipeIngredient' => ['1 Tasse Mehl', '1 Ei'],
			'recipeInstructions' => ['Mischen', 'Braten'],
			'nutrition' => ['calories' => '450 kcal'],
		];
		$json = json_encode($full, JSON_UNESCAPED_UNICODE);

		return [
			'plain json' => [$json, $full, $full],
			'markdown code fence' => ["Here is the recipe:\n```json\n$json\n```\nEnjoy!", $full, $full],
			'partial answer' => [
				'{"name": "Foo", "unknown": "bar"}',
				array_merge(self::DEFAULTS, ['name' => 'Foo']),
				['name' => 'Foo'],
			],
		];
	}

	public function testParseResponseUsesFilteredValues(): void {
		$this->jsonFilter->method('apply')->willReturn(array_merge(self::DEFAULTS, [
			'@type' => 'Recipe',
			'name' => 'Cleaned',
			'keywords' => 'a,b',
			'url' => '',
		]));

		$this->assertEquals(
			['name' => 'Cleaned', 'keywords' => 'a,b'],
			$this->dut->parseResponse('{"name": " Cleaned ", "keywords": ["a", "b"]}')
		);
	}

	public function testParseResponseFilterFails(): void {
		$this->jsonFilter->method('apply')->willThrowException(new InvalidRecipeException('Bad ingredients'));

		$this->expectException(InvalidAssistantResponseException::class);
		$this->dut->parseResponse('{"recipeIngredient": "flour"}');
	}

	/**
	 * @dataProvider dpParseResponseInvalid
	 */
	public function testParseResponseInvalid(string $output): void {
		$this->jsonFilter->expects($this->never())->method('apply');

		$this->expectException(InvalidAssistantResponseException::class);
		$this->dut->parseResponse($output);
	}

	public static function dpParseResponseInvalid(): array {
		return [
			'empty' => [''],
			'prose' => ['I cannot do that.'],
			'broken json' => ['{"name": "Foo"'],
			'json list' => ['["a", "b"]'],
			'empty object' => ['{}'],
			'no recipe fields' => ['{"foo": "bar"}'],
		];
	}
}
