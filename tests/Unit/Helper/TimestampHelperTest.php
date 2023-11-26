<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Exception\InvalidTimestampException;
use OCA\Cookbook\Helper\TimestampHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class TimestampHelperTest extends TestCase {
	/** @var TimestampHelper */
	private $dut;

	protected function setUp(): void {
		/** @var IL10N|Stub $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->dut = new TimestampHelper($l);
	}

	public function dpIso() {
		return [
			['2000-01-01T01:01:00+00:00', '2000-01-01T01:01:00+00:00'],
			['2000-01-01T01:01:00Z', '2000-01-01T01:01:00+00:00'],
			['2000-01-01T01:01:00+05:30', '2000-01-01T01:01:00+05:30'],
			['2000-01-01T01:01:00-05:30', '2000-01-01T01:01:00-05:30'],
			['2000-01-01T01:01:00.5+00:00', '2000-01-01T01:01:00+00:00'],
			['2000-01-01T01:01:00,12+00:00', '2000-01-01T01:01:00+00:00'],
			['2000-01-01T01:01:00.123+00:00', '2000-01-01T01:01:00+00:00'],
			['2000-01-01T01:01:00.123Z', '2000-01-01T01:01:00+00:00'],
			['2000-01-01T01:01:00.123+01:00', '2000-01-01T01:01:00+01:00'],
			['2000-01-01T01:01:00.123-01:00', '2000-01-01T01:01:00-01:00'],


			['20000101T01:01:00.123-01:00', '2000-01-01T01:01:00-01:00'],
			['20000101T01:01:00-01:00', '2000-01-01T01:01:00-01:00'],

			['2014-W01-2T01:01:00.123-01:00', '2013-12-31T01:01:00-01:00'],
			['2014-W01-2T01:01:00,123-01:00', '2013-12-31T01:01:00-01:00'],
			['2014-W01-2T01:01:00-01:00', '2013-12-31T01:01:00-01:00'],

			['2014W012T01:01:00.123-01:00', '2013-12-31T01:01:00-01:00'],
			['2014W012T01:01:00,123-01:00', '2013-12-31T01:01:00-01:00'],
			['2014W012T01:01:00-01:00', '2013-12-31T01:01:00-01:00'],
		];
	}

	/** @dataProvider dpIso */
	public function testIsoFormat($input, $expectedOutput) {
		$this->assertEquals($expectedOutput, $this->dut->parseTimestamp($input));
	}

	public function dpFail() {
		return [
			['Just some text'],
			[''],
			['   '],
			['123:45:46'], // Missing date
			['T12:10:00'], // Missing date
			['2000-01-01'], // Missing time
			['2000-01-01 01:01:00+00:00'], // Missing separator T
			['2000-01-01T01:01:00'], // Missing timezone
			['2000-01-01 T 01:01:00+00:00'], // There should be no spaces
			['01.11.2000T01:01:00+00:00'], // Wrong date
			['2000/12/01T01:01:00+00:00'], // Wrong date
		];
	}

	/** @dataProvider dpFail */
	public function testFailedFormat($input) {
		$this->expectException(InvalidTimestampException::class);
		$this->dut->parseTimestamp($input);
	}
}
