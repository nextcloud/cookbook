<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Helper\TextCleanupHelper;
use PHPUnit\Framework\TestCase;

class TextCleanupHelperTest extends TestCase {
	/** @var TextCleanupHelper */
	private $dut;

	protected function setUp(): void {
		$this->dut = new TextCleanupHelper();
	}

	public function dpCleanUp() {
		return [
			['Just some    text', true, true, 'Just some text'],
			['Just some    text', false, false, 'Just some text'],
			["A\r\nfew\nlines with/NL", true, false, 'A few lines with/NL'],
			["A/few/words with\nslashes", false, true, "A_few_words with\nslashes"],
			["Both/newlines\nand//slashes", true, true, 'Both_newlines and__slashes'],
			["With\tTabs", false, false, "With Tabs"],
			['With\\backslash\\', false, false, 'With_backslash_'],
			['&auml;&Uuml;&szlig;&amp;', false, false, 'äÜß&'],
		];
	}

	/**
	 * @dataProvider dpCleanUp
	 * @param mixed $str
	 * @param mixed $rmNl
	 * @param mixed $rmSl
	 * @param mixed $expected
	 */
	public function testCleanUp($str, $rmNl, $rmSl, $expected) {
		$ret = $this->dut->cleanUp($str, $rmNl, $rmSl);
		$this->assertEquals($expected, $ret);
	}
}
