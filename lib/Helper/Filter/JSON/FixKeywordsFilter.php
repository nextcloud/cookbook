<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Fix the keyword list.
 *
 * The keywords are assumed to be a comma-separated list of single keywords.
 * If no such a string exists, it is initialized.
 *
 * This filter merges an array into such a list.
 * The keywords are individually trimmed, multiple spaces are replaced by a single space.
 * Also, special chars that should not be part of a keyword are removed.
 */
class FixKeywordsFilter extends AbstractJSONFilter {
	private const KEYWORDS = 'keywords';

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	/** @var TextCleanupHelper */
	private $textCleaner;

	public function __construct(IL10N $l, LoggerInterface $logger, TextCleanupHelper $textCleanupHelper) {
		$this->l = $l;
		$this->logger = $logger;
		$this->textCleaner = $textCleanupHelper;
	}

	public function apply(array &$json): bool {
		if (!isset($json[self::KEYWORDS])) {
			$json[self::KEYWORDS] = '';
			return true;
		}

		if (!is_string($json[self::KEYWORDS]) && ! is_array($json[self::KEYWORDS])) {
			$this->logger->info($this->l->t('Could not parse the keywords for recipe {recipe}.', ['recipe' => $json['name']]));
			$json[self::KEYWORDS] = '';
			return true;
		}

		if (is_string($json[self::KEYWORDS])) {
			$keywords = explode(',', $json[self::KEYWORDS]);
		} else {
			$keywords = $json[self::KEYWORDS];
		}

		// Temp variable for forwarding
		$textCleaner = $this->textCleaner;

		$keywords = array_map(function ($kw) use ($textCleaner) {
			// Remove any HTML tags
			$kw = strip_tags($kw);
			// Trim the keyword from both sides
			$kw = trim($kw);
			// Replace multiple blanks by single one
			$kw = preg_replace('/\s+/', ' ', $kw);
			// Clean up keywords
			$kw = $textCleaner->cleanUp($kw);

			return $kw;
		}, $keywords);
		// Filter out empty keywords
		$keywords = array_filter($keywords, fn ($x) => ($x !== ''));
		// Remove duplicates
		$keywords = array_unique($keywords);

		$csk = implode(',', $keywords);
		$changed = ($csk !== $json[self::KEYWORDS]);
		$json[self::KEYWORDS] = $csk;

		return $changed;
	}
}
