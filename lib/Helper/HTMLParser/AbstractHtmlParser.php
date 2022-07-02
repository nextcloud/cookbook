<?php

namespace OCA\Cookbook\Helper\HTMLParser;

use OCA\Cookbook\Exception\HtmlParsingException;
use OCP\IL10N;

abstract class AbstractHtmlParser {
	/**
	 * @var IL10N
	 */
	protected $l;

	public function __construct(IL10N $l10n) {
		$this->l = $l10n;
	}

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
