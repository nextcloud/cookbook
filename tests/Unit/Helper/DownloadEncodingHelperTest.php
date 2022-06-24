<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Helper\DownloadEncodingHelper;
use PHPUnit\Framework\TestCase;

class DownloadEncodingHelperTest extends TestCase {
	/** @var DownloadEncodingHelper */
	private $dut;

	protected function setUp(): void {
		$this->dut = new DownloadEncodingHelper();
	}

	public function dpEncodings() {
		return [
			['iso-8859-1']
		];
	}

	/**
	 * @dataProvider dpEncodings
	 * @param mixed $encoding
	 */
	public function testEncodeToUTF8($encoding) {
		$unencoded = file_get_contents(__DIR__ . "/DownloadEncodingHelper/$encoding.orig");
		$encoded = file_get_contents(__DIR__ . "/DownloadEncodingHelper/$encoding.utf8");

		$testEncoded = $this->dut->encodeToUTF8($unencoded, $encoding);
		$this->assertEquals($encoded, $testEncoded);
	}

	public function testEncodeUTF8ToUTF8() {
		$encodings = $this->dpEncodings();

		$testString = '';
		foreach ($encodings as $enc) {
			$fileContent = file_get_contents(__DIR__ . "/DownloadEncodingHelper/{$enc[0]}.utf8");
			$testString .= $fileContent;
		}

		$this->assertEquals($testString, $this->dut->encodeToUTF8($testString, 'UTF-8'));
	}
}
