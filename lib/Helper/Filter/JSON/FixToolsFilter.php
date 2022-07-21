<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidRecipeException;
use OCP\IL10N;
use OCP\ILogger;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;

/**
 * Fix the tools list.
 *
 * The tools entry is mandatory for the recipes and an array.
 * This filter ensures, an entry is present.
 *
 * If no entry is found, an empty array is added.
 * If there is already an array present, the entries are cleaned up to prevent malicious chars to be present.
 */
class FixToolsFilter extends AbstractJSONFilter {
	private const TOOLS = 'tools';

	/** @var IL10N */
	private $l;

	/** @var ILogger */
	private $logger;

	/** @var TextCleanupHelper */
	private $textCleaner;

	public function __construct(IL10N $l, ILogger $logger, TextCleanupHelper $textCleanupHelper) {
		$this->l = $l;
		$this->logger = $logger;
		$this->textCleaner = $textCleanupHelper;
	}

	public function apply(array &$json): bool {
		if (!isset($json[self::TOOLS])) {
			$json[self::TOOLS] = [];
			return true;
		}

		if (!is_array($json[self::TOOLS])) {
			throw new InvalidRecipeException($this->l->t('Could not parse recipe tools. It is no array.'));
		}

		$tools = $json[self::TOOLS];

		$tools = array_map(function ($t) {
			$t = trim($t);
			$t = preg_replace('/\s+/', ' ', $t);
			$t = $this->textCleaner->cleanUp($t);
			return $t;
		}, $tools);
		$tools = array_filter($tools, fn ($t) => ($t));

		$changed = $tools !== $json[self::TOOLS];
		$json[self::TOOLS] = $tools;
		return $changed;
	}
}
