<?php

namespace OCA\Cookbook\tests\Unit\Service;

use DOMDocument;
use OCP\IL10N;
use OCP\ILogger;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Helper\HtmlToDomParser;
use OCA\Cookbook\Exception\ImportException;
use OCA\Cookbook\Exception\NoDownloadWasCarriedOutException;
use OCA\Cookbook\Helper\DownloadHelper;
use PHPUnit\Framework\MockObject\MockObject;
use OCA\Cookbook\Service\HtmlDownloadService;
use OCA\Cookbook\Helper\HTMLFilter\HtmlEntityDecodeFilter;

/**
 * @covers \OCA\Cookbook\Service\HtmlDownloadService
 */
class HtmlDownloadServiceTest extends TestCase {
	/**
	 * @var HtmlEntityDecodeFilter|MockObject
	 */
	private $htmlEntityDecodeFilter;
	/**
	 * @var ILogger
	 */
	private $ilogger;
	/**
	 * @var IL10N
	 */
	private $il10n;
	/**
	 * @var HtmlToDomParser|MockObject
	 */
	private $htmlParser;
	/** @var DownloadHelper|MockObject */
	private $downloadHelper;

	/**
	 * @var HtmlDownloadService
	 */
	private $sut;

	/**
	 * @var HtmlDownloadServiceTest
	 */
	public static $instance;

	public function setUp(): void {
		parent::setUp();

		self::$instance = $this;

		$this->htmlEntityDecodeFilter = $this->createMock(HtmlEntityDecodeFilter::class);
		$this->htmlEntityDecodeFilter->method('apply')->willReturnArgument(0);
		$this->ilogger = $this->createStub(ILogger::class);
		$this->il10n = $this->createStub(IL10N::class);
		$this->htmlParser = $this->createMock(HtmlToDomParser::class);
		$this->downloadHelper = $this->createMock(DownloadHelper::class);

		$this->sut = new HtmlDownloadService($this->htmlEntityDecodeFilter, $this->ilogger, $this->il10n, $this->htmlParser, $this->downloadHelper);
	}

	public function testDownloadInvalidUrl() {
		$url = 'http:///example.com';
		$this->expectException(ImportException::class);
		$this->sut->downloadRecipe($url);
	}

	public function testDownloadFailing() {
		$url = 'http://example.com';
		$this->downloadHelper->expects($this->once())
			->method('downloadFile')
			->willThrowException(new NoDownloadWasCarriedOutException());
		$this->expectException(ImportException::class);
		$this->sut->downloadRecipe($url);
	}

	public function dpBadStatus() {
		return [
			[180], [199], [300], [404]
		];
	}

	/**
	 * @dataProvider dpBadStatus
	 * @param mixed $status
	 */
	public function testDownloadBadStatus($status) {
		$url = 'http://example.com';
		$this->downloadHelper->expects($this->once())
			->method('downloadFile');
		$this->downloadHelper->method('getStatus')->willReturn($status);
		$this->expectException(ImportException::class);
		$this->sut->downloadRecipe($url);
	}

	public function testDownload() {
		$url = 'http://example.com';
		$content = 'The content of the html file';
		$dom = $this->createStub(DOMDocument::class);
		$state = 12345;

		$this->downloadHelper->expects($this->once())
			->method('downloadFile');
		$this->downloadHelper->method('getStatus')->willReturn(200);
		$this->downloadHelper->method('getContent')->willReturn($content);
		$this->htmlParser->expects($this->once())->method('loadHtmlString')
			->with(
				$this->anything(),
				$this->equalTo($url),
				$this->equalTo($content)
			)->willReturn($dom);
		$this->htmlParser->method('getState')->willReturn($state);

		$ret = $this->sut->downloadRecipe($url);
		$this->assertEquals($state, $ret);

		$this->assertSame($dom, $this->sut->getDom());
	}
}
