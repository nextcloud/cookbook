<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Service;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser;
use OCA\Cookbook\Helper\HTMLParser\HttpMicrodataParser;
use OCA\Cookbook\Service\RecipeExtractionService;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecipeExtractionServiceTest extends TestCase {
	/**
	 * @var IL10N
	 */
	private $l;

	protected function setUp(): void {
		parent::setUp();

		$this->l = $this->createStub(IL10N::class);
	}

	/**
	 * @dataProvider dataProvider
	 * @param bool $jsonSuccess
	 * @param bool $microdataSuccess
	 * @param bool $exceptionExpected
	 */
	public function testParsingDelegation($jsonSuccess, $microdataSuccess, $exceptionExpected): void {
		/** @var HttpJsonLdParser|MockObject $jsonParser */
		$jsonParser = $this->createMock(HttpJsonLdParser::class);
		/** @var HttpMicrodataParser|MockObject $microdataParser */
		$microdataParser = $this->createMock(HttpMicrodataParser::class);

		$document = $this->createStub(\DOMDocument::class);
		$url = 'http://example.com';
		$expectedObject = [new \stdClass()];

		if ($jsonSuccess) {
			$jsonParser->expects($this->once())
				->method('parse')
				->with($document, $url)
				->willReturn($expectedObject);

			$microdataParser->expects($this->never())->method('parse');
		} else {
			$jsonParser->expects($this->once())
				->method('parse')
				->with($document, $url)
				->willThrowException(new HtmlParsingException());

			if ($microdataSuccess) {
				$microdataParser->expects($this->once())
					->method('parse')
					->with($document, $url)
					->willReturn($expectedObject);
			} else {
				$microdataParser->expects($this->once())
					->method('parse')
					->with($document, $url)
					->willThrowException(new HtmlParsingException());
			}
		}

		$sut = new RecipeExtractionService($jsonParser, $microdataParser, $this->l);

		try {
			$ret = $sut->parse($document, $url);

			$this->assertEquals($expectedObject, $ret);
		} catch (HtmlParsingException $ex) {
			$this->assertTrue($exceptionExpected);
		}
	}

	/**
	 * @dataProvider dataProviderJson
	 */
	public function testParseJson(string $input): void {
		$json = '{"@type":"Recipe"}';
		$expectedObject = ['@type' => 'Recipe'];

		/** @var HttpJsonLdParser|MockObject $jsonParser */
		$jsonParser = $this->createMock(HttpJsonLdParser::class);
		$jsonParser->expects($this->once())
			->method('parseJsonLd')
			->with($json)
			->willReturn($expectedObject);

		$sut = new RecipeExtractionService($jsonParser, $this->createStub(HttpMicrodataParser::class), $this->l);

		$this->assertEquals($expectedObject, $sut->parseJson($input));
	}

	public static function dataProviderJson(): array {
		return [
			'plain' => ['{"@type":"Recipe"}'],
			'script tag' => ['<script type="application/ld+json">{"@type":"Recipe"}</script>'],
			'script tag with whitespace' => [" <script type=\"application/ld+json\">{\"@type\":\"Recipe\"}</script>\n"],
		];
	}

	public static function dataProvider() {
		return [
			[true, false, false],
			[false, true, false],
			[false, false, true],
		];
	}
}
