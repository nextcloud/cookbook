<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\tests\Unit\Helper\HtmlToDomParserTest;

function libxml_use_internal_errors($prev) {
	return HtmlToDomParserTest::$instance->triggerXmlInternalErrors($prev);
}

function libxml_get_errors() {
	return HtmlToDomParserTest::$instance->triggerXmlGetErrors();
}

function libxml_clear_errors() {
	HtmlToDomParserTest::$instance->triggerXmlClearErrors();
}

namespace OCA\Cookbook\tests\Unit\Helper;

use OCP\IL10N;
use DOMDocument;
use Exception;
use LibXMLError;
use OCA\Cookbook\Exception\ImportException;
use OCP\ILogger;
use ReflectionProperty;
use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Helper\HtmlToDomParser;
use PHPUnit\Framework\MockObject\MockObject;

class XMLMocking {
	public function useInternalErrors(bool $prev): bool {
		return $prev;
	}
	public function getErrors(): array {
		return [];
	}
	public function clearErrors(): void {
	}
}

/**
 * @coversDefaultClass OCA\Cookbook\Helper\HtmlToDomParser
 * @covers ::<private>
 * @covers ::<protected>
 */
class HtmlToDomParserTest extends TestCase {

	/**
	 * @var ILogger|MockObject
	 */
	private $logger;
	/**
	 * @var IL10N|MockObject
	 */
	private $l;

	/**
	 * @var HtmlToDomParser
	 */
	private $sut;

	/**
	 * @var HtmlToDomParserTest
	 */
	public static $instance;

	/**
	 * @var XMLMocking|MockObject
	 */
	private $xmlMock;

	public function setUp(): void {
		parent::setUp();

		self::$instance = $this;
		$this->xmlMock = $this->createMock(XMLMocking::class);

		$this->logger = $this->createStub(ILogger::class);

		$this->l = $this->createStub(IL10N::class);
		$this->l->method('t')->will($this->returnArgument(0));

		$this->sut = new HtmlToDomParser($this->logger, $this->l);
	}
	
	/**
	 * @covers ::__construct
	 */
	public function testConstructor(): void {
		$loggerProp = new ReflectionProperty(HtmlToDomParser::class, 'logger');
		$loggerProp->setAccessible(true);
		$this->assertSame($this->logger, $loggerProp->getValue($this->sut));

		$lProp = new ReflectionProperty(HtmlToDomParser::class, 'l');
		$lProp->setAccessible(true);
		$this->assertSame($this->l, $lProp->getValue($this->sut));
	}

	/**
	 * @covers ::loadHtmlString
	 * @covers ::getState
	 * @dataProvider dataProviderParsing
	 */
	public function testParsing($successDomParsing, $stateAtEnd, $errors, $numErrors, $expectsError) {
		/**
		 * @var MockObject|DOMDocument $dom
		 */
		$dom = $this->createMock(DOMDocument::class);
		$dom->expects($this->once())
			->method('loadHtml')
			->willReturn($successDomParsing);

		$this->xmlMock->expects($this->exactly(2))
			->method('useInternalErrors')
			->withConsecutive([true], [false])
			->willReturnOnConsecutiveCalls(false, true);
		
			
		$this->xmlMock->expects($this->once())
			->method('getErrors')
			->willReturn($errors);
			
		if ($expectsError) {
			$this->expectException(Exception::class);
		} else {
			$this->xmlMock->expects($this->once())
				->method('clearErrors');
			
			$this->logger->expects($this->exactly($numErrors[2]))
				->method('notice');
			$this->logger->expects($this->exactly($numErrors[1]))
				->method('warning');
			$this->logger->expects($this->exactly($numErrors[0]))
				->method('error');
		}

		$url = 'http://example.com/recipe';
		$html = 'Foo Bar Baz';
		
		$this->assertEquals(4, $this->sut->getState());

		try {
			$this->sut->loadHtmlString($dom, $url, $html);

			$this->assertTrue($successDomParsing);
		} catch (ImportException $ex) {
			$this->assertFalse($successDomParsing);
		}

		$this->assertEquals($stateAtEnd, $this->sut->getState());
	}

	public function dataProviderParsing() {
		return [
			"failedParsing" => [
				false,
				null,
				[],
				[0,0,0],
				false,
			],
			"parsingWithoutErrors" => [
				true,
				HtmlToDomParser::PARSING_SUCCESS,
				[],
				[0,0,0],
				false,
			],
			"parsingWithSingleWarning" => [
				true,
				HtmlToDomParser::PARSING_WARNING,
				[
					$this->getXMLError(1, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				],
				[0,0,1],
				false,
			],
			"parsingWithSingleError" => [
				true,
				HtmlToDomParser::PARSING_ERROR,
				[
					$this->getXMLError(1, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
				],
				[0,1,0],
				false,
			],
			"parsingWithSingleFatal" => [
				true,
				HtmlToDomParser::PARSING_FATAL_ERROR,
				[
					$this->getXMLError(1, LIBXML_ERR_FATAL, '/file', 1, 2, 'The message'),
				],
				[1,0,0],
				false,
			],
			"parsingWithAllTypes" => [
				true,
				HtmlToDomParser::PARSING_FATAL_ERROR,
				[
					$this->getXMLError(1, LIBXML_ERR_FATAL, '/file', 1, 2, 'The message'),
					$this->getXMLError(2, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
					$this->getXMLError(3, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
					$this->getXMLError(4, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
					$this->getXMLError(5, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
					$this->getXMLError(6, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				],
				[1,2,3],
				false,
			],
			"parsingWithAllTypes" => [
				true,
				HtmlToDomParser::PARSING_ERROR,
				[
					$this->getXMLError(2, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
					$this->getXMLError(2, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
					$this->getXMLError(3, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
					$this->getXMLError(4, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
					$this->getXMLError(5, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
					$this->getXMLError(5, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
					$this->getXMLError(5, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
					$this->getXMLError(6, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
					$this->getXMLError(6, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				],
				[0,2,3],
				false,
			],
			"parsingWithWrongXMLError" => [
				true,
				HtmlToDomParser::PARSING_SUCCESS,
				[
					$this->getXMLError(2, LIBXML_ERR_NONE, '/file', 1, 2, 'The message'),
				],
				[0,0,0],
				false,
			],
		];
	}

	private function getXMLError($code, $level, $file, $line, $column, $msg): LibXMLError {
		$ret = new LibXMLError();
		$ret->code = $code;
		$ret->level = $level;
		$ret->file = $file;
		$ret->line = $line;
		$ret->column = $column;
		$ret->message = $msg;
		return $ret;
	}

	public function testLogging(): void {
		/**
		 * @var MockObject|DOMDocument $dom
		 */
		$dom = $this->createMock(DOMDocument::class);
		$dom->expects($this->once())
			->method('loadHtml')
			->willReturn(true);

		$this->xmlMock->expects($this->once())
			->method('getErrors')
			->willReturn([
				$this->getXMLError(2, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
				$this->getXMLError(2, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
				$this->getXMLError(3, LIBXML_ERR_ERROR, '/file', 1, 2, 'The message'),
				$this->getXMLError(4, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				$this->getXMLError(5, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				$this->getXMLError(5, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				$this->getXMLError(5, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				$this->getXMLError(6, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
				$this->getXMLError(6, LIBXML_ERR_WARNING, '/file', 1, 2, 'The message'),
			]);
		
		$url = 'http://example.com/recipe';

		$this->logger->expects($this->exactly(3))
			->method('notice');
		$this->logger->expects($this->exactly(2))
			->method('warning')
			->withConsecutive(
				["libxml: Error %u occurred %u times while parsing %s. First time it occurred in line %u and column %u: The message"],
				["libxml: Error %u occurred %u times while parsing %s. First time it occurred in line %u and column %u: The message"]
			);
		$this->logger->expects($this->exactly(0))
			->method('error');

		$html = 'Foo Bar Baz';
		
		$this->assertEquals(4, $this->sut->getState());

		$this->sut->loadHtmlString($dom, $url, $html);
	}

	public function triggerXmlInternalErrors(bool $param): bool {
		return $this->xmlMock->useInternalErrors($param);
	}

	public function triggerXmlGetErrors(): array {
		return $this->xmlMock->getErrors();
	}

	public function triggerXmlClearErrors(): void {
		$this->xmlMock->clearErrors();
	}
}
