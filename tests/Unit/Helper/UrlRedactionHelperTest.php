<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Cookbook\tests\Unit\Helper;

use OCA\Cookbook\Helper\UrlRedactionHelper;
use PHPUnit\Framework\TestCase;

class UrlRedactionHelperTest extends TestCase {
	public static function dataProvider(): array {
		return [
			'plain' => ['https://example.com/recipe/1', 'https://example.com/recipe/1'],
			'credentials' => ['https://user:secret@example.com/recipe', 'https://example.com/recipe'],
			'query and fragment' => ['https://example.com/recipe?token=abc#top', 'https://example.com/recipe'],
			'port' => ['http://example.com:8080/r', 'http://example.com:8080/r'],
			'no path' => ['https://example.com', 'https://example.com'],
			'invalid' => ['not a url', '[invalid URL]'],
			'null' => [null, '[invalid URL]'],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testRedact(?string $url, string $expected): void {
		$this->assertEquals($expected, UrlRedactionHelper::redact($url));
	}
}
