<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCP\IL10N;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCA\Cookbook\Helper\Filter\JSON\FixToolsFilter;

class FixToolsFilterTest extends TestCase {
	/** @var FixToolsFilter */
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

		$this->dut = new FixToolsFilter($l, $logger, $this->textCleanupHelper);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;
		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['tools'] = [];
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
		$recipe['tools'] = $startVal;

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->stub['tools'] = $expectedVal;
		$this->assertEquals($changed, $ret);
		$this->assertEquals($this->stub, $recipe);
	}

	public function testApplyString() {
		$recipe = $this->stub;
		$recipe['tools'] = 'some text';

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);
		$this->expectException(InvalidRecipeException::class);

		$this->dut->apply($recipe);
	}
}
