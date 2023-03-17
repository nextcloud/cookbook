<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\RecipeIdTypeFilter;
use Test\TestCase;

class RecipeIdTypeFilterTest extends TestCase {
	/** @var RecipeIdTypeFilter */
	private $dut;

	protected function setUp(): void {
		$this->dut = new RecipeIdTypeFilter();
	}

	public function dp() {
		$stub = [
			'name' => 'The name of the recipe',
			'servings' => 5,
			'ingredients' => ['Spaghetti', 'Tomatoes', 'Salt'],
		];

		$stub['id'] = 123;
		$expected = $stub;
		yield [$stub, $expected, true];

		$stub['id'] = '123';
		yield [$stub, $expected, false];
	}

	/** @dataProvider dp */
	public function testFilter($input, $expected, $changed) {
		$ret = $this->dut->apply($input);

		$this->assertEquals($expected, $input);
		$this->assertEquals($changed, $ret);
		$this->assertTrue(is_string($input['id']));
	}
}
