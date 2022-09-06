<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\FixRecipeYieldFilter;
use OCP\IL10N;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class FixRecipeYieldFilterTest extends TestCase {
	/** @var FixRecipeYieldFilter */
	private $dut;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		/** @var LoggerInterface */
		$logger = $this->createStub(LoggerInterface::class);

		$this->dut = new FixRecipeYieldFilter($l, $logger);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;
		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['recipeYield'] = '';
		$this->assertEquals($this->stub, $recipe);
	}

	public function dp() {
		return [
			[1,1,false],
			[5,5,false],
			[3.0,3,true],
			[3.4,3,true],
			[[2], 2, true],
			[[4.6], 4, true],
			["3", 3, true],
			["123 456", 456, true],
			[[4,5], 5, true],
			['', 1, true],
			['one two three', 1, true],
			[null, null, false]
		];
	}

	/** @dataProvider dp */
	public function testApply($startVal, $expectedVal, $changed) {
		$recipe = $this->stub;
		$recipe['recipeYield'] = $startVal;

		$ret = $this->dut->apply($recipe);

		$this->stub['recipeYield'] = $expectedVal;
		$this->assertEquals($changed, $ret);
		$this->assertEquals($this->stub, $recipe);
	}
}
