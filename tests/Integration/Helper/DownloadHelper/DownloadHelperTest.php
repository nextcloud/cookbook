<?php

namespace OCA\Cookbook\Helper;

function curl_exec($ch) {
	return \OCA\Cookbook\tests\Integration\Helper\DownloadHelper\DownloadHelperTest::mock_curl_exec($ch);
}

namespace OCA\Cookbook\tests\Integration\Helper\DownloadHelper;

use OCA\Cookbook\Exception\NoDownloadWasCarriedOutException;
use OCA\Cookbook\Helper\DownloadHelper;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @covers OCA\Cookbook\Helper\DownloadHelper
 * @covers OCA\Cookbook\Exception\NoDownloadWasCarriedOutException
 */
class DownloadHelperTest extends TestCase {
	/** @var DownloadHelper */
	private $dut;

	protected function setUp(): void {
		/** @var IL10N|Stub */
		$l = $this->createStub(IL10N::class);
		$l->method('t')->willReturnArgument(0);

		$this->dut = new DownloadHelper($l);

		if (file_exists('/www/.htaccess')) {
			unlink('/www/.htaccess');
		}

		self::$useCurlMock = false;
	}

	protected function tearDown(): void {
		if (file_exists('/www/.htaccess')) {
			unlink('/www/.htaccess');
		}
	}

	public function testGetContentNotDownloaded() {
		$this->expectException(NoDownloadWasCarriedOutException::class);
		$this->dut->getContent();
	}

	public function testGetContentTypeNotDownloaded() {
		$this->expectException(NoDownloadWasCarriedOutException::class);
		$this->dut->getContentType();
	}

	public function testGetStatusNotDownloaded() {
		$this->expectException(NoDownloadWasCarriedOutException::class);
		$this->dut->getStatus();
	}

	public function testDownload() {
		$str = "This is the content of the file.\n";
		file_put_contents('/www/test.txt', $str);
		$this->dut->downloadFile('http://www/test.txt');
		$this->assertEquals($str, $this->dut->getContent());
		$this->assertEquals(200, $this->dut->getStatus());
	}

	public function dpContentType() {
		return [
			['text/html;charset=ascii'],
			['text/html;charset=UTF-8'],
			['image/jpeg'],
			// ['text/html;charset=ascii'],
		];
	}

	/**
	 * @dataProvider dpContentType
	 * @param mixed $contentType
	 */
	public function testContentType($contentType) {
		copy(__DIR__ . '/res/content-type.php', '/www/test.php');
		$this->dut->downloadFile('http://www/test.php?content=' . urlencode($contentType));
		$this->assertEquals(trim($contentType), $this->dut->getContentType());
		$this->assertEquals(200, $this->dut->getStatus());
	}

	public function testDownload404() {
		if (file_exists('/www/test.txt')) {
			unlink('/www/test.txt');
		}
		$this->dut->downloadFile('http://www/test.txt');
		$this->assertEquals(404, $this->dut->getStatus());
	}

	public function testFailedDownload() {
		$this->expectException(NoDownloadWasCarriedOutException::class);
		// Using mock here as the timeout causes the test to be really slow
		self::$useCurlMock = true;
		$this->dut->downloadFile('http://www2/test.txt');
	}

	private static $useCurlMock = false;
	public static function mock_curl_exec($ch) {
		if (self::$useCurlMock) {
			throw new NoDownloadWasCarriedOutException();
		} else {
			return curl_exec($ch);
		}
	}

	public function testNoContentType() {
		copy(__DIR__ . '/res/htaccess', '/www/.htaccess');
		// $this->expectException(NoDownloadWasCarriedOutException::class);
		$this->dut->downloadFile('http://www/test.php');
		$this->assertNull($this->dut->getContentType());
	}
}
