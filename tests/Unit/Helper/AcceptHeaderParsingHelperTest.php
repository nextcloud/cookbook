<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Helper\AcceptHeaderParsingHelper;
use PHPUnit\Framework\TestCase;

class AcceptHeaderParsingHelperTest extends TestCase {

	/**
	 * @var AcceptHeaderParsingHelper
	 */
	private $dut;

	protected function setUp(): void {
		parent::setUp();

		$this->dut = new AcceptHeaderParsingHelper();
	}

	public function testDefaultExtensions() {
		$ret = $this->dut->getDefaultExtensions();

		$this->assertEquals(['jpg'], $ret);
	}

	public function dataProvider() {
		yield ['image/jpeg', ['jpg']];
		yield ['image/jpg', ['jpg']];
		yield ['image/png', ['png']];
		yield ['image/svg+xml', ['svg']];
		yield ['image/*', ['jpg', 'png', 'svg']];
		yield ['*/*', ['jpg', 'png', 'svg']];
		yield ['image/jpeg, image/*', ['jpg', 'png', 'svg']];
		yield ['image/jpeg;q=0.9, image/*;q=0.5', ['jpg', 'png', 'svg']];
		yield ['image/webn, image/jpeg', ['jpg']];
		yield ['image/webn', []];
		yield ['image/png;q=0.5, image/jpg', ['jpg', 'png']];
		yield ['image/png;q=0.5, image/jpeg;q=0.3, image/svg+xml', ['svg', 'png', 'jpg']];
		yield ['image/png, image/jpeg;q=0.3, image/svg+xml;q=0.9', ['png', 'svg', 'jpg']];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testParseHeader($header, $expected) {
		$this->assertEquals($expected, $this->dut->parseHeader($header));
	}
}
