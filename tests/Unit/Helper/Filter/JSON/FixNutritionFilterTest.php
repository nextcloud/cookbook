<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCP\IL10N;
use OCP\ILogger;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use OCA\Cookbook\Helper\Filter\JSON\FixNutritionFilter;

class FixNutritionFilterTest extends TestCase {
	/** @var FixNutritionFilter */
	private $dut;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(ILogger::class);

		$this->dut = new FixNutritionFilter($l, $logger);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['nutrition'] = [];
		$this->assertEquals($this->stub, $recipe);
	}

	public function testInvalidString() {
		$recipe = $this->stub;
		$recipe['nutrition'] = 'Invalid text';

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['nutrition'] = [];
		$this->assertEquals($this->stub, $recipe);
	}

	public function dpApply() {
		return [
			[[], [], false],
			[['a'], ['a'], false],
			[['a', null], ['a'], true],
			[['a', []], ['a'], true],
		];
	}

	/** @dataProvider dpApply */
	public function testApply($oldValue, $expectedValue, $shouldChange) {
		$recipe = $this->stub;
		$recipe['nutrition'] = $oldValue;

		$ret = $this->dut->apply($recipe);

		$this->stub['nutrition'] = $expectedValue;
		$this->assertEquals($this->stub, $recipe);
		$this->assertEquals($shouldChange, $ret);
	}
}
