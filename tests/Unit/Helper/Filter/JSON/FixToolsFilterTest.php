<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\FixToolsFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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

		$this->stub['tool'] = [];
		$this->assertEquals($this->stub, $recipe);
	}

	public static function dp() {
		return [
			[['a','b','c'], ['a','b','c'], false],
			[[' a  ',''], ['a'], true],
			[[' a  ','', 'b'], ['a', 'b'], true],
			['a', ['a'], true],
			['', [], true],
			// The text cleaner is only stubbed, so the multiple inline spaces are not fixed here.
			["  a   \tb ", ["a   \tb"], true],
		];
	}

	/** @dataProvider dp */
	public function testApply($startVal, $expectedVal, $changed) {
		$recipe = $this->stub;
		$recipe['tool'] = $startVal;

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->stub['tool'] = $expectedVal;
		$this->assertEquals($changed, $ret);
		$this->assertEquals($this->stub, $recipe);
	}
}
