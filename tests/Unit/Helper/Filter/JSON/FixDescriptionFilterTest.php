<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\FixDescriptionFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class FixDescriptionFilterTest extends TestCase {
	/** @var FixDescriptionFilter */
	private $dut;

	/** @var TextCleanupHelper|MockObject */
	private $textCleanupHelper;

	/** @var array */
	private $stub;

	protected function setUp(): void {
		/** @var Stub|IL10N */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);
		$logger = $this->createStub(LoggerInterface::class);

		$this->textCleanupHelper = $this->createMock(TextCleanupHelper::class);

		$this->dut = new FixDescriptionFilter($l, $logger, $this->textCleanupHelper);

		$this->stub = [
			'name' => 'The name of the recipe',
			'id' => 1234,
		];
	}

	public function testNonExisting() {
		$recipe = $this->stub;

		$this->assertTrue($this->dut->apply($recipe));

		$this->stub['description'] = '';
		$this->assertEquals($this->stub, $recipe);
	}

	public function dpApply() {
		return [
			["abc", "abc", "abc", false],
			["abc\n\ndef", "abc\n\ndef", "abc\n\ndef", false],
			["abc\n\n", "abc\n\n", "abc", true],
			// ["abc\n\n", "abc", true],
		];
	}

	/** @dataProvider dpApply */
	public function testApply($oldValue, $cleaned, $expectedValue, $shouldChange) {
		$recipe = $this->stub;
		$recipe['description'] = $oldValue;

		$this->textCleanupHelper->expects($this->once())->method('cleanUp')
			->with($oldValue, false, false)->willReturn($cleaned);

		$ret = $this->dut->apply($recipe);

		$this->stub['description'] = $expectedValue;
		$this->assertEquals($this->stub, $recipe);
		$this->assertEquals($shouldChange, $ret);
	}
}
