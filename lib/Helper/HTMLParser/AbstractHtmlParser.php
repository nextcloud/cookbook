<?php

// SPDX-FileCopyrightText: 2026 Nextcloud cookbook contributors
//
// SPDX-License-Identifier: AGPL-3.0-only OR AGPL-3.0-or-later

namespace OCA\Cookbook\Helper\HTMLParser;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCA\Cookbook\Helper\UrlRedactionHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

abstract class AbstractHtmlParser {
	/**
	 * @var IL10N
	 */
	protected $l;

	protected LoggerInterface $logger;

	public function __construct(
		IL10N $l10n,
		LoggerInterface $logger,
		protected UrlRedactionHelper $urlRedactionHelper,
	) {
		$this->l = $l10n;
		$this->logger = $logger;
	}

	abstract public function getName(): string;

	/**
	 * Extract the recipe from the given document.
	 *
	 * @param \DOMDocument $document The document to parse
	 * @param ?string $url The URL of the recipe to import
	 * @return array The JSON content in the document as a PHP array
	 * @throws HtmlParsingException If the parsing was not successful
	 */
	abstract public function parse(\DOMDocument $document, ?string $url): array;
}
