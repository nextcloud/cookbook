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

		$parser = new HttpJsonLdParser($l, $jsonService);

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

		$parser = new HttpJsonLdParser($l, $jsonService);

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

	public static function dataProviderJsonLd(): array {
		$context = 'https://schema.org';
		$recipe = ['@context' => $context, '@type' => 'Recipe', 'name' => 'Soup'];
		$graphRecipe = ['@type' => 'Recipe', 'name' => 'Soup'];
		$other = ['@type' => 'WebPage', 'name' => 'Page'];
		$image = ['@type' => 'ImageObject', '@id' => '#img', 'url' => 'image.jpg'];
		$author = ['@type' => 'Person', '@id' => '#author', 'name' => 'Jane'];

		return [
			'plain recipe' => [$recipe, $recipe],
			'array of objects' => [[$other, $recipe], $recipe],
			'graph' => [['@context' => $context, '@graph' => [$other, $graphRecipe]], $recipe],
			'array with graph' => [[['@context' => $context, '@graph' => [$other, $graphRecipe]]], $recipe],
			'type array' => [['@context' => $context, '@type' => ['Recipe', 'Thing'], 'name' => 'Soup'], $recipe],
			'no recipe' => [['@context' => $context, '@graph' => [$other]], null],
			'scalar' => ['Soup', null],
			'prefixed type' => [['@context' => $context, '@type' => 'schema:Recipe', 'name' => 'Soup'], $recipe],
			'iri type' => [['@context' => $context, '@type' => 'http://schema.org/Recipe', 'name' => 'Soup'], $recipe],
			'main entity' => [['@context' => $context, '@type' => 'WebPage', 'mainEntity' => $graphRecipe], $recipe],
			'array with main entity' => [[['@context' => $context, '@type' => 'WebPage', 'mainEntity' => $graphRecipe]], $recipe],
			'nested graph' => [['@context' => $context, '@graph' => [['@graph' => [$other, $graphRecipe]]]], $recipe],
			'image reference' => [
				['@context' => $context, '@graph' => [$graphRecipe + ['image' => ['@id' => '#img'], 'author' => ['@id' => '#author']], $image, $author]],
				$recipe + ['image' => $image, 'author' => ['@id' => '#author']],
			],
			'image reference list' => [
				['@context' => $context, '@graph' => [$graphRecipe + ['image' => [['@id' => '#img'], ['@id' => '#missing'], 'other.jpg']], $image]],
				$recipe + ['image' => [$image, ['@id' => '#missing'], 'other.jpg']],
			],
			'no context' => [$graphRecipe, null],
			'no context assumed' => [$graphRecipe, $recipe, true],
			'array without context assumed' => [[$other, $graphRecipe], $recipe, true],
			'own context kept when assumed' => [['@context' => 'https://example.com', '@type' => 'Recipe', 'name' => 'Soup'], null, true],
		];
	}

	/**
	 * @dataProvider dataProviderJsonLd
	 * @covers ::parseJsonLd
	 * @param mixed $input
	 */
	public function testParseJsonLd($input, ?array $expected, bool $assumeSchemaContext = false): void {
		$parser = new HttpJsonLdParser($this->createStub(IL10N::class), new JsonService());

		if ($expected === null) {
			$this->expectException(HtmlParsingException::class);
		}

		$this->assertEquals($expected, $parser->parseJsonLd(json_encode($input), $assumeSchemaContext));
	}

	/**
	 * @covers ::parseJsonLd
	 */
	public function testParseJsonLdScriptTag(): void {
		$parser = new HttpJsonLdParser($this->createStub(IL10N::class), new JsonService());
		$recipe = ['@context' => 'https://schema.org', '@type' => 'Recipe', 'name' => 'Soup'];

		$res = $parser->parseJsonLd(' <script type="application/ld+json">' . json_encode($recipe) . "</script>\n");

		$this->assertEquals($recipe, $res);
	}

	/**
	 * @covers ::parseJsonLd
	 */
	public function testParseJsonLdInvalid(): void {
		$parser = new HttpJsonLdParser($this->createStub(IL10N::class), new JsonService());

		$this->expectException(HtmlParsingException::class);

		$parser->parseJsonLd('not json');
	}
}
