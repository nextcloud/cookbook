<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\Output;

use OCP\IL10N;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use OCA\Cookbook\Helper\Filter\Output\EnsureNutritionPresentFilter;

class EnsureNutritionPresentFilterTest extends TestCase {
	/** @var EnsureNutritionPresentFilter */
	private $dut;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(LoggerInterface::class);

		$this->dut = new EnsureNutritionPresentFilter($l, $logger);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;

		$ret = $this->dut->apply($recipe);

		$this->stub['nutrition'] = ['@type' => 'NutritionInformation'];
		$this->assertEquals($this->stub, $recipe);
		$this->assertTrue($ret);
	}

	public function dpApply() {
		return [
			[[], ['@type' => 'NutritionInformation'], false],
			[['a'], ['a', '@type' => 'NutritionInformation'], false],
			[['@type' => 'NutritionInformation'], ['@type' => 'NutritionInformation'], false],
			[['a', '@type' => 'NutritionInformation'], ['a', '@type' => 'NutritionInformation'], false],
		];
	}

	/** @dataProvider dpApply */
	public function testApply($oldValue, $expectedValue, $shouldChange) {
		$recipe = $this->stub;
		$recipe['nutrition'] = $oldValue;
		$recipe['nutrition']['@type'] = 'NutritionInformation';

		$ret = $this->dut->apply($recipe);

		$this->stub['nutrition'] = $expectedValue;
		$this->assertEquals($this->stub, $recipe);
		$this->assertEquals($shouldChange, $ret);
	}
}
