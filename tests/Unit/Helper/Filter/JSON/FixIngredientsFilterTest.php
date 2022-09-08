<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCP\IL10N;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCA\Cookbook\Exception\InvalidRecipeException;
use OCA\Cookbook\Helper\Filter\JSON\FixIngredientsFilter;

class FixIngredientsFilterTest extends TestCase {
	/** @var FixIngredientsFilter */
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

		$this->dut = new FixIngredientsFilter($l, $logger, $this->textCleanupHelper);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;
		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['recipeIngredient'] = [];
		$this->assertEquals($this->stub, $recipe);
	}

	public function dp() {
		return [
			[['a','b','c'], ['a','b','c'], false],
			[[' a  ',''], ['a'], true],
		];
	}

	/** @dataProvider dp */
	public function testApply($startVal, $expectedVal, $changed) {
		$recipe = $this->stub;
		$recipe['recipeIngredient'] = $startVal;

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->stub['recipeIngredient'] = $expectedVal;
		$this->assertEquals($changed, $ret);
		$this->assertEquals($this->stub, $recipe);
	}

	public function testApplyString() {
		$recipe = $this->stub;
		$recipe['recipeIngredient'] = 'some text';

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);
		$this->expectException(InvalidRecipeException::class);

		$this->dut->apply($recipe);
	}
}
