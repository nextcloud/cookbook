<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Exception\InvalidDurationException;
use OCA\Cookbook\Helper\ISO8601DurationHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class ISO8601DurationHelperTest extends TestCase {
	/** @var ISO8601DurationHelper */
	private $dut;

	protected function setUp(): void {
		/** @var IL10N|Stub $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->dut = new ISO8601DurationHelper($l);
	}

	public function dpHM() {
		return [
			['0:01', 'PT0H1M'],
			['0:21', 'PT0H21M'],
			['3:21', 'PT3H21M'],
			['3:00', 'PT3H0M'],
			['13:00', 'PT13H0M'],
			['   13:00  ', 'PT13H0M'],
		];
	}

	/** @dataProvider dpHM */
	public function testHMFormat($input, $expectedOutput) {
		$this->assertEquals($expectedOutput, $this->dut->parseDuration($input));
	}

	public function dpIso() {
		return [
			['PT0H1M', 'PT0H1M0S'],
			['PT0H21M', 'PT0H21M0S'],
			['PT3H21M', 'PT3H21M0S'],
			['PT3H0M', 'PT3H0M0S'],
			['PT3H', 'PT3H0M0S'],
			['PT13H0M', 'PT13H0M0S'],
			['PT0H60M61S', 'PT1H1M1S'],
		];
	}

	/** @dataProvider dpIso */
	public function testIsoFormat($input, $expectedOutput) {
		$this->assertEquals($expectedOutput, $this->dut->parseDuration($input));
	}

	public function dpFail() {
		return [
			['Just some text'],
			[''],
			['   '],
			['123:45:46'],
			['T12H'],
		];
	}

	/** @dataProvider dpFail */
	public function testFailedFormat($input) {
		$this->expectException(InvalidDurationException::class);
		$this->dut->parseDuration($input);
	}
}
