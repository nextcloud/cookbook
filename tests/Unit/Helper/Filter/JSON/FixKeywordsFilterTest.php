<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCP\IL10N;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Stub;
use OCA\Cookbook\Helper\TextCleanupHelper;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Helper\Filter\JSON\FixKeywordsFilter;

class FixKeywordsFilterTest extends TestCase {
	/** @var FixKeywordsFilter */
	private $dut;

	/** @var TextCleanupHelper|MockObject */
	private $textCleanupHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		/** @var LoggerInterface */
		$logger = $this->createStub(LoggerInterface::class);

		$this->textCleanupHelper = $this->createMock(TextCleanupHelper::class);

		$this->dut = new FixKeywordsFilter($l, $logger, $this->textCleanupHelper);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;
		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['keywords'] = '';
		$this->assertEquals($this->stub, $recipe);
	}

	public function dp() {
		return [
			[12, '', true],
			['a,b,c', 'a,b,c', false],
			[['a','b','c'], 'a,b,c', true],
			['a b ,,  , c  d ', 'a b,c d', true],
			[['a b ', '', ' c  d '], 'a b,c d', true],
			['a,<i>  b </i>, c', 'a,b,c', true],
			['', '', false],
			[[], '', true]
		];
	}

	/** @dataProvider dp */
	public function testApply($startVal, $expectedVal, $changed) {
		$recipe = $this->stub;
		$recipe['keywords'] = $startVal;

		$this->textCleanupHelper->method('cleanUp')->willReturnArgument(0);

		$ret = $this->dut->apply($recipe);

		$this->stub['keywords'] = $expectedVal;
		$this->assertEquals($changed, $ret);
		$this->assertEquals($this->stub, $recipe);
	}
}
