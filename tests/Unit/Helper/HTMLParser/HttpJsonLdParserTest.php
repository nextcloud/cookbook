<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Helper\HTMLParser;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser;
use OCA\Cookbook\Service\JsonService;
use OCP\IL10N;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser
 * @author christian
 *
 */
class HttpJsonLdParserTest extends TestCase {
	public static function dataProvider(): array {
		return [
			'case01' => ['case01.html', true, 'case01.json'],
			'case02' => ['case02.html', true, 'case02.json'],
			'case03' => ['case03.html', false, null],
			'case04' => ['case04.html', false, null],
			'case05' => ['case05.html', false, null],
			'case06' => ['case06.html', true, 'case06.json'],
			'case07' => ['case07.html', true, 'case07.json'],
			'case08' => ['case08.html', true, 'case08.json'],
			'case09' => ['case09.html', true, 'case09.json'],
			'case10' => ['case10.html', true, 'case10.json'],
			//'case11' => ['case11.html', true, 'case11.json'],
			'case12' => ['case12.html', true, 'case12.json'],
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

		$logger = $this->createStub(LoggerInterface::class);

		$parser = new HttpJsonLdParser($l, $logger, $jsonService);

		$lProperty = new \ReflectionProperty(HttpJsonLdParser::class, 'l');
		$lProperty->setAccessible(true);
		$lSaved = $lProperty->getValue($parser);
		$this->assertSame($l, $lSaved);
	}

	/**
	 * @dataProvider dataProvider
	 * @covers ::parse
	 * @param mixed $file
	 * @param mixed $valid
	 * @param mixed $jsonFile
	 */
	public function testHTMLFile($file, $valid, $jsonFile): void {
		$jsonService = new JsonService();
		/**
		 * @var IL10N $l
		 */
		$l = $this->createStub(IL10N::class);

		$logger = $this->createStub(LoggerInterface::class);

		$parser = new HttpJsonLdParser($l, $logger, $jsonService);

		$content = file_get_contents(__DIR__ . "/res_JsonLd/$file");

		$document = new \DOMDocument();
		$document->loadHTML($content);

		try {
			$res = $parser->parse($document, 'http://example.com');

			$jsonDest = file_get_contents(__DIR__ . "/res_JsonLd/$jsonFile");
			$expected = json_decode($jsonDest, true);

			$this->assertEquals($expected, $res);
			$this->assertTrue($valid);
		} catch (HtmlParsingException $ex) {
			$this->assertFalse($valid);
		}
	}

	public static function dataProviderImageReference(): array {
		$context = 'https://schema.org';
		$recipe = ['@type' => 'Recipe', 'name' => 'Soup'];
		$image = ['@type' => 'ImageObject', '@id' => '#img', 'url' => 'image.jpg'];

		return [
			'image reference' => [
				['@context' => $context, '@graph' => [$recipe + ['image' => ['@id' => '#img']], $image]],
				['@context' => $context] + $recipe + ['image' => $image],
			],
			'unknown image reference' => [
				['@context' => $context, '@graph' => [$recipe + ['image' => ['@id' => '#missing']], $image]],
				['@context' => $context] + $recipe + ['image' => ['@id' => '#missing']],
			],
		];
	}

	/**
	 * @dataProvider dataProviderImageReference
	 * @covers ::parse
	 */
	public function testImageReference(array $input, array $expected): void {
		$parser = new HttpJsonLdParser($this->createStub(IL10N::class), $this->createStub(LoggerInterface::class), new JsonService());

		$document = new \DOMDocument();
		$document->loadHTML('<html><head><script type="application/ld+json">' . json_encode($input) . '</script></head></html>');

		$this->assertEquals($expected, $parser->parse($document, 'http://example.com'));
	}
}
