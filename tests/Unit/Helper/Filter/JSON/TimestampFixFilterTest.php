<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\JSON\TimestampFixFilter;
use PHPUnit\Framework\TestCase;

class TimestampFixFilterTest extends TestCase {
	/** @var TimestampFixFilter */
	private $dut;

	protected function setUp(): void {
		$this->dut = new TimestampFixFilter();
	}

	public static function dp() {
		yield ['2024-01-02T13:15:10', '2024-01-02T13:15:10', false];
		yield ['2024-01-02 13:15:10', '2024-01-02T13:15:10', true];

		yield ['2024-01-02T13:15:10+00:00', '2024-01-02T13:15:10+00:00', false];
		yield ['2024-01-02 13:15:10+00:00', '2024-01-02T13:15:10+00:00', true];
	}

	private function getStub($time) {
		return [
			'dateCreated' => $time,
			'dateModified' => $time,
		];
	}

	/** @dataProvider dp */
	public function testFilter($inputTime, $expectedOutputTime, $changed) {
		$input = $this->getStub($inputTime);
		$expectedOutput = $this->getStub($expectedOutputTime);

		$ret = $this->dut->apply($input);
		$this->assertEquals($changed, $ret);
		$this->assertSame($expectedOutput, $input);
	}

	// }
}
