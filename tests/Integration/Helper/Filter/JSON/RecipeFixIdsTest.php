<?php

namespace OCA\Cookbook\tests\Integration\Helper\Filter\JSON;

use OCA\Cookbook\AppInfo\Application;
use OCA\Cookbook\Helper\Filter\Output\RecipeStubFilter;
use PHPUnit\Framework\TestCase;

class RecipeFixIdsTest extends TestCase {
	/** @var RecipeStubFilter */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$app = new Application();
		$container = $app->getContainer();
		$this->dut = $container->get(RecipeStubFilter::class);
	}

	public function dp() {
		$stub = [
			'name' => 'The name of te recipe',
			'date' => '1970-01-12',
		];

		$src = $stub;
		$src['recipe_id'] = 123;

		$expected = $stub;
		$expected['recipe_id'] = 123;
		$expected['id'] = '123';
		yield 'only numeric recipe_id' => [$src, $expected];

		$src['recipe_id'] = $expected['recipe_id'] = '123';
		yield 'only string recipe_id' => [$src, $expected];

		$src['id'] = '234';
		$expected['id'] = '234';
		yield 'with string id' => [$src, $expected];

		$src['recipe_id'] = $expected['recipe_id'] = 123;
		yield 'mixed string and int id recipe_id' => [$src, $expected];
	}

	/**
	 * @dataProvider dp
	 * @param mixed $original
	 * @param mixed $expected
	 */
	public function testRecipeStubs($original, $expected) {
		$ret = $this->dut->apply($original);
		$this->assertSame($expected, $ret);
	}

}
