<?php

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Exception\CouldNotGuessEncodingException;
use OCA\Cookbook\Helper\EncodingGuessingHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\EncodingGuessingHelper
 * @covers OCA\Cookbook\Exception\CouldNotGuessEncodingException
 */
class EncodingGuessingHelperTest extends TestCase {
	/** @var EncodingGuessingHelper */
	private $dut;

	protected function setUp(): void {
		/** @var IL10N|Stub $l */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->dut = new EncodingGuessingHelper($l);
	}

	public function dpPureContentType() {
		return [
			['text/text; charset=utf-8', 'utf-8'],
			['text/text; boundary=foo ; charset=UTF-16', 'UTF-16'],
			['text/text;boundary=foo;charset=iso-8859-1;param=value', 'iso-8859-1'],
		];
	}

	/**
	 * @dataProvider dpPureContentType
	 * @param mixed $ct
	 * @param mixed $enc
	 */
	public function testGuessEncodingFromContentType($ct, $enc) {
		$this->assertEquals($enc, $this->dut->guessEncoding('', $ct));
	}

	public function dpPureContent() {
		return [
			['contentA.html', 'iso-8859-1'],
			['contentB.html', 'UTF-16'],
		];
	}

	/**
	 * @dataProvider dpPureContent
	 * @param mixed $filename
	 * @param mixed $enc
	 */
	public function testGuessEncodingFromContentNoContentType($filename, $enc) {
		$content = file_get_contents(__DIR__ . "/EncodingGuessingHelper/$filename");
		$this->assertEquals($enc, $this->dut->guessEncoding($content, null));
	}

	public function testGuessEncodingNoEncoding() {
		$this->expectException(CouldNotGuessEncodingException::class);
		$this->dut->guessEncoding('Some text', 'text/text;boundary=foo');
	}

	/**
	 * @dataProvider dpPureContentType
	 * @param mixed $ct
	 * @param mixed $enc
	 */
	public function testGuessEncodingBothPresent($ct, $enc) {
		$content = file_get_contents(__DIR__ . '/EncodingGuessingHelper/contentA.html');
		$this->assertEquals($enc, $this->dut->guessEncoding($content, $ct));
	}
}
