<?php

namespace OCA\Cookbook\tests\Unit\Helper\HTMLFilter;

use OCA\Cookbook\Helper\HTMLFilter\HtmlEncodingFilter;
use PHPUnit\Framework\TestCase;

class HtmlEncodingFilterTest extends TestCase {
	/** @var HtmlEncodingFilter */
	private $dut;

	protected function setUp(): void {
		$this->dut = new HtmlEncodingFilter();
	}

	public function dp() {
		return [
			['<html><head></head><body></body></html>', '<?xml encoding="UTF-8"><html><head></head><body></body></html>'],
			['<?xml boundary="abc" encoding="ISO-8859-1"><html><head></head><body></body></html>', '<?xml boundary="abc" encoding="ISO-8859-1"><html><head></head><body></body></html>'],
			["<?xml boundary='abc' encoding='ISO-8859-1'><html><head></head><body></body></html>", "<?xml boundary='abc' encoding='ISO-8859-1'><html><head></head><body></body></html>"],
		];
	}

	/**
	 * @dataProvider dp
	 * @param mixed $input
	 * @param mixed $desiredOutput
	 */
	public function testApply($input, $desiredOutput) {
		$this->dut->apply($input);
		$this->assertEquals($desiredOutput, $input);
	}
}
