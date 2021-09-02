<?php

namespace OCA\Cookbook\Service;

use OCA\Cookbook\tests\Unit\Service\HtmlDownloadServiceTest;

function file_get_contents($url, $useIncludePath, $context) {
    return HtmlDownloadServiceTest::$instance->triggerGetContent($url, $useIncludePath, $context);
}

namespace OCA\Cookbook\tests\Unit\Service;

use DOMDocument;
use OCP\IL10N;
use OCP\ILogger;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Helper\HtmlToDomParser;
use OCA\Cookbook\Exception\ImportException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use OCA\Cookbook\Service\HtmlDownloadService;
use OCA\Cookbook\Helper\HTMLFilter\HtmlEntityDecodeFilter;

class MockFileGetter {
    public function getIt($url, $internal, $context) { return ''; }
}

/**
 * @coversDefaultClass \OCA\Cookbook\Service\HtmlDownloadService
 * @covers ::<protected>
 * @covers ::<private>
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
    /**
     * @var HtmlDownloadService
     */
    private $sut;

    /**
     * @var HtmlDownloadServiceTest
     */
    public static $instance;

    /**
     * @var boolean
     */
    private $runRealFunction;

    /**
     * @var MockFileGetter|MockObject
     */
    private $mockFunction;

    public function setUp(): void {
        parent::setUp();

        self::$instance = $this;

        $this->htmlEntityDecodeFilter = $this->createMock(HtmlEntityDecodeFilter::class);
        $this->htmlEntityDecodeFilter->method('apply')->willReturnArgument(0);
        $this->ilogger = $this->createStub(ILogger::class);
        $this->il10n = $this->createStub(IL10N::class);
        $this->htmlParser = $this->createMock(HtmlToDomParser::class);

        $this->mockFunction = $this->createMock(MockFileGetter::class);
        $this->runRealFunction = true;

        $this->sut = new HtmlDownloadService($this->htmlEntityDecodeFilter, $this->ilogger, $this->il10n, $this->htmlParser);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void {
        $filtersProp = new ReflectionProperty(HtmlDownloadService::class, 'htmlFilters');
        $loggerProp = new ReflectionProperty(HtmlDownloadService::class, 'logger');
        $lProp = new ReflectionProperty(HtmlDownloadService::class, 'l');

        $filtersProp->setAccessible(true);
        $loggerProp->setAccessible(true);
        $lProp->setAccessible(true);

        $this->assertSame([$this->htmlEntityDecodeFilter], $filtersProp->getValue($this->sut));
        $this->assertSame($this->ilogger, $loggerProp->getValue($this->sut));
        $this->assertSame($this->il10n, $lProp->getValue($this->sut));
    }

    /**
     * covers ::getDom
     * @todo This must be checked
     */
    public function oldTestGetterDom(): void {
        $domProp = new ReflectionProperty(HtmlDownloadService::class, 'dom');
        $domProp->setAccessible(true);
        /**
         * @var DOMDocument $dom
         */
        $dom = $this->createStub(DOMDocument::class);
        $domProp->setValue($this->sut, $dom);

        $this->assertSame($dom, $this->sut->getDom());
    }

    public function triggerGetContent($path, $useInternalPath, $context) {
        if($this->runRealFunction) {
            return file_get_contents($path, $useInternalPath, $context);
        } else {
            return $this->mockFunction->getIt($path, $useInternalPath, $context);
        }
    }

    /**
     * @dataProvider dataProviderFakeDownload
     * @covers ::downloadRecipe
     * @covers ::getDom
     * @covers \OCA\Cookbook\Exception\ImportException
     */
    public function testFakeDownload($url, $urlValid, $fetchedValue, $parserState, $fetchValid): void {
        $this->runRealFunction = false;

        $dom = new DOMDocument();

        if($urlValid) {
            // Mock file_get_contents
            $this->mockFunction->expects($this->once())->method('getIt')->with(
                $this->equalTo($url),
                $this->anything(),
                $this->callback(function ($context) {
                    $opts = stream_context_get_options($context);
                    if(!isset($opts['http'])) return false;
                    if(!isset($opts['http']['method']) || $opts['http']['method'] !== 'GET') return false;
                    return true;
                })
            )->willReturn($fetchedValue);
        } else {
            $this->mockFunction->expects($this->never())->method('getIt');
        }

        if($urlValid && $fetchValid) {
            $this->htmlEntityDecodeFilter->expects($this->once())->method('apply');

            $this->htmlParser->expects($this->once())->method('loadHtmlString')->with(
                $this->anything(),
                $this->equalTo($url),
                $this->equalTo($fetchedValue)
            )->willReturn($dom);
            $this->htmlParser->method('getState')->willReturn($parserState);

        } else {
            $this->htmlEntityDecodeFilter->expects($this->never())->method('apply');
            $this->htmlParser->expects($this->never())->method('loadHtmlString');
        }

        $this->assertNull($this->sut->getDom());

        try {
            $result = $this->sut->downloadRecipe($url);

            $this->assertTrue($urlValid && $fetchValid);
            $this->assertSame($dom, $this->sut->getDom());
            $this->assertEquals($parserState, $result);
        } catch (ImportException $ex) {
            $this->assertFalse($urlValid && $fetchValid);
        }
    }

    public function dataProviderFakeDownload() {
        return [
            'invalidURL' => ['http:///example.com', false, null, null, false],
            'validURL' => ['http://example.com', true, 'Here comes the text', 1, true],
            'invalidFetch' => ['http://example.com', true, false, 1, false],
        ];
    }

    /**
     * @dataProvider dataProviderRealDownload
     * @covers ::downloadRecipe
     */
    public function testRealDownload($data) {
        $url = 'http://www/test.html';
        $this->runRealFunction = true;

        if(file_exists('/www/test.html')) {
            unlink('/www/test.html');
        }
        file_put_contents('/www/test.html', $data);

        $this->htmlParser->expects($this->once())->method('loadHtmlString')->with(
            $this->anything(),
            $this->equalTo($url),
            $this->equalTo($data)
        );

        $this->htmlParser->method('getState')->willReturn(0);

        $this->sut->downloadRecipe($url);
    }

    public function dataProviderRealDownload() {
        yield [
            '<html><head><title>foo</title></head><body></body></html>'
        ];
    }

}
