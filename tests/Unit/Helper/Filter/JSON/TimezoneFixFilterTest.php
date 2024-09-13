<?php

namespace OCA\Cookbook\tests\Unit\Helper\Filter\JSON;

use DateTime;
use DateTimeZone;
use OCA\Cookbook\Helper\Filter\JSON\TimezoneFixFilter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TimezoneFixFilterTest extends TestCase {
	/** @var TimezoneFixFilter */
	private $dut;

	public function setUp(): void {
		$logger = $this->createStub(LoggerInterface::class);
		$this->dut = new TimezoneFixFilter($logger);
	}

	public function db() {
		$defaultTimezone = date_default_timezone_get();
		$tz = new DateTimeZone($defaultTimezone);
		$now = new DateTime('now', $tz);
		$offset = $tz->getOffset($now);
		$hours = sprintf('%+03d00', $offset / 3600);

		yield ["2024-05-20T10:12:00$hours", "2024-05-20T10:12:00$hours", false];
		yield ['2024-05-20T10:12:00', "2024-05-20T10:12:00$hours", true];
		yield ["2024-05-20T10:12:00.20$hours", "2024-05-20T10:12:00.20$hours", false];
		yield ['2024-05-20T10:12:00.20', "2024-05-20T10:12:00.20$hours", true];
		yield ["2024-05-20T1:12:00$hours", "2024-05-20T1:12:00$hours", false];
		yield ['2024-05-20T1:12:00', "2024-05-20T1:12:00$hours", true];
	}

	/** @dataProvider db */
	public function testFilter($inputDate, $expectedDate, $changed) {
		$input = $this->getStub($inputDate);
		$expected = $this->getStub($expectedDate);

		$ret = $this->dut->apply($input);

		$this->assertEquals($expected, $input);
		$this->assertEquals($changed, $ret);
	}

	private function getStub($date) {
		return [
			'name' => 'Test Recipe',
			'id' => 123,
			'dateCreated' => $date,
			'dateModified' => $date,
		];
	}
}
