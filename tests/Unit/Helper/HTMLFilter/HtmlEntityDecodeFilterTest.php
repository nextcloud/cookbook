<?php

namespace OCA\Cookbook\tests\Unit\Helper\HTMLFilter;

use OCA\Cookbook\Helper\HTMLFilter\HtmlEntityDecodeFilter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\Cookbook\Helper\HTMLFilter\HtmlEntityDecodeFilter
 */
class HtmlEntityDecodeFilterTest extends TestCase {
	/**
	 * @dataProvider dataProvider
	 * @param mixed $testString
	 */
	public function testDecoder($testString): void {
		$sut = new HtmlEntityDecodeFilter();
		$encoded = htmlentities($testString);
		$decoded = $encoded;
		$sut->apply($decoded);
		$this->assertEquals($testString, $decoded);
	}

	public static function dataProvider() {
		yield ['abc'];
		yield ['Test <b>äößå'];
	}

	/**
	 * @dataProvider dataProviderExplicit
	 * @param mixed $encoded
	 * @param mixed $expected
	 */
	public function testDecoderExplicit($encoded, $expected): void {
		$sut = new HtmlEntityDecodeFilter();
		$decoded = $encoded;
		$sut->apply($decoded);
		$this->assertEquals($expected, $decoded);
	}

	public static function dataProviderExplicit() {
		yield ['Test &lt;b&gt;&auml;&ouml;&szlig;&aring;', 'Test <b>äößå'];
	}
}
