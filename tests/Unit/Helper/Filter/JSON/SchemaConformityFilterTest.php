<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\SchemaConformityFilter;
use PHPUnit\Framework\TestCase;

class SchemaConformityFilterTest extends TestCase {
	/** @var SchemaConformityFilter */
	private $dut;

	protected function setUp(): void {
		$this->dut = new SchemaConformityFilter();
	}

	public function dp() {
		return [
			[false, null, false, null, true],
			[true, null, false, null, true],
			[false, null, true, null, true],
			[true, 1, true, 'bar', true],
			[true, 'http://schema.org', false, null, true],
			[true, 'https://schema.org', true, 'Recipe', true],
			[true, 'http://schema.org', true, 'Recipe', false],
		];
	}

	/**
	 * @dataProvider dp
	 * @param mixed $hasContext
	 * @param mixed $context
	 * @param mixed $hasType
	 * @param mixed $type
	 * @param mixed $isChanged
	 */
	public function testFilter($hasContext, $context, $hasType, $type, $isChanged) {
		$recipeStub = [
			'name' => 'The name',
			'id' => 1234,
		];

		$recipe = $recipeStub;

		if ($hasContext) {
			$recipe['@context'] = $context;
		}
		if ($hasType) {
			$recipe['@type'] = $type;
		}

		$this->assertEquals($isChanged, $this->dut->apply($recipe));

		$recipeExpected = $recipeStub;
		$recipeExpected['@context'] = 'http://schema.org';
		$recipeExpected['@type'] = 'Recipe';

		$this->assertEquals($recipeExpected, $recipe);
	}
}
