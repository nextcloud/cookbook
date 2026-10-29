<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Service;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Helper\HTMLParser\HttpJsonLdParser;
use OCA\Cookbook\Helper\HTMLParser\HttpMicrodataParser;
use OCA\Cookbook\Helper\UrlRedactionHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

class RecipeExtractionService {
	/**
	 * @var array
	 */
	private $parsers;

	/**
	 * @var IL10N
	 */
	private $l;

	private LoggerInterface $logger;

	public function __construct(HttpJsonLdParser $jsonParser, HttpMicrodataParser $microdataParser,
		IL10N $l10n, LoggerInterface $logger) {
		$this->parsers = [$jsonParser, $microdataParser];
		$this->l = $l10n;
		$this->logger = $logger;
	}

	/**
	 * Parse a DOM document using all registered parsers
	 *
	 * @param \DOMDocument $document The document to parse
	 * @param ?string $url The URL of the recipe to be parsed
	 * @throws HtmlParsingException If no parser was able to successfully parse the document
	 * @return array The data as returned from the parser
	 */
	public function parse(\DOMDocument $document, ?string $url): array {
		$failures = [];

		foreach ($this->parsers as $parser) {
			try {
				return $parser->parse($document, $url);
			} catch (HtmlParsingException $ex) {
				$parserName = (new \ReflectionClass($parser))->getShortName();
				$failures[] = "$parserName: {$ex->getMessage()}";
				$this->logger->info('Parser {parser} failed for URL {url}: {reason}', [
					'parser' => $parserName, 'url' => UrlRedactionHelper::redact($url), 'reason' => $ex->getMessage(),
				]);
			}
		}

		$detail = implode('; ', $failures);
		$this->logger->warning('All parsers failed for URL {url}. Details: {detail}', [
			'url' => UrlRedactionHelper::redact($url), 'detail' => $detail,
		]);
		throw new HtmlParsingException(
			$this->l->t('No parser found for the given import.') . ' ' . $detail
		);
	}
}
