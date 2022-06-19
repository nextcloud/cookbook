<?php

namespace OCA\Cookbook\tests\Unit\Helper\HTMLParser;

use PHPUnit\Framework\TestCase;
use OCA\Cookbook\Service\JsonService;
use OCP\IL10N;
use OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser;
use OCA\Cookbook\Exception\HtmlParsingException;

/**
 * @coversDefaultClass \OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser
 * @covers ::<protected>
 * @covers ::<private>
 * @author christian
 *
 */
class HttpJsonLdParserTest extends TestCase {
	public function dataProvider(): array {
		return [
			'caseA' => ['caseA.html', true, 'caseA.json'],
			'caseB' => ['caseB.html', true, 'caseB.json'],
			'caseC' => ['caseC.html', false, null],
			'caseD' => ['caseD.html', false, null],
			'caseE' => ['caseE.html', false, null],
			'caseF' => ['caseF.html', true, 'caseF.json'],
			'caseG' => ['caseG.html', true, 'caseG.json'],
			'caseH' => ['caseH.html', true, 'caseH.json'],
			'caseI' => ['caseI.html', true, 'caseI.json'],
		];
	}

	/**
	 * @covers ::__construct
	 * @covers \OCA\Cookbook\Helper\HTMLParser\AbstractHtmlParser::__construct
	 */
	public function testConstructor(): void {
		/**
		 * @var JsonService $jsonService
		 */
		$jsonService = $this->createStub(JsonService::class);
		/**
		 * @var IL10N $l
		 */
		$l = $this->createStub(IL10N::class);

		$parser = new HttpJsonLdParser($l, $jsonService);

		$lProperty = new \ReflectionProperty(HttpJsonLdParser::class, 'l');
		$lProperty->setAccessible(true);
		$lSaved = $lProperty->getValue($parser);
		$this->assertSame($l, $lSaved);
	}

	/**
	 * @dataProvider dataProvider
	 * @covers ::parse
	 */
	public function testHTMLFile($file, $valid, $jsonFile): void {
		$jsonService = new JsonService();
		/**
		 * @var IL10N $l
		 */
		$l = $this->createStub(IL10N::class);

		$parser = new HttpJsonLdParser($l, $jsonService);

		$content = file_get_contents(__DIR__ . "/res_JsonLd/$file");

		$document = new \DOMDocument();
		$document->loadHTML($content);

		try {
			$res = $parser->parse($document);

			$jsonDest = file_get_contents(__DIR__ . "/res_JsonLd/$jsonFile");
			$expected = json_decode($jsonDest, true);

			$this->assertTrue($valid);
			$this->assertEquals($expected, $res);
		} catch (HtmlParsingException $ex) {
			$this->assertFalse($valid);
		}
	}
}
