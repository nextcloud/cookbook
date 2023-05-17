<?php

namespace OCA\Cookbook\tests\Integration\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCA\Cookbook\Helper\Filter\JSON\FixIngredientsFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FixIngredientsFilterTest extends TestCase {
	/** @var FixIngredientsFilter */
	private $dut;

	/** @var TextCleanupHelper */
	private $textCleanupHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		/** @var LoggerInterface */
		$logger = $this->createStub(LoggerInterface::class);

		$this->textCleanupHelper = new TextCleanupHelper();

		$this->dut = new FixIngredientsFilter($l, $logger, $this->textCleanupHelper);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function dp() {
		return [
			[['a','b','c'], ['a','b','c'], false],
			[[' a  ',''], ['a'], true],
			[["  a   \tb ",'   c  '],['a b','c'],true],
			[["a\nb"],["a\nb"],false],
		];
	}

	/** @dataProvider dp */
	public function testApply($startVal, $expectedVal, $changed) {
		$recipe = $this->stub;
		$recipe['recipeIngredient'] = $startVal;

		$ret = $this->dut->apply($recipe);

		$this->stub['recipeIngredient'] = $expectedVal;
		$this->assertEquals($changed, $ret);
		$this->assertEquals($this->stub, $recipe);
	}

	public function testApplyString() {
		$recipe = $this->stub;
		$recipe['recipeIngredient'] = 'some text';

		$this->expectException(InvalidRecipeException::class);

		$this->dut->apply($recipe);
	}
}
