<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCP\IL10N;
use OCP\ILogger;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\TextCleanupHelper;

/**
 * Fix the description of a recipe.
 *
 * The description must be present in all cases.
 * If is is not present, this filter will insert the empty string instead.
 *
 * Apart from that the description is cleaned from malicious chars.
 */
class FixDescriptionFilter extends AbstractJSONFilter {
	private const NUTRITION = 'description';

	/** @var IL10N */
	private $l;

	/** @var ILogger */
	private $logger;

	/** @var TextCleanupHelper */
	private $textCleaner;

	public function __construct(
		IL10N $l,
		ILogger $logger,
		TextCleanupHelper $textCleanupHelper
	) {
		$this->l = $l;
		$this->logger = $logger;
		$this->textCleaner = $textCleanupHelper;
	}

	public function apply(array &$json): bool {
		if (!isset($json[self::NUTRITION])) {
			$json[self::NUTRITION] = '';
			return true;
		}

		$description = $this->textCleaner->cleanUp($json[self::NUTRITION], false);
		$description = trim($description);

		$changed = $description !== $json[self::NUTRITION];
		$json[self::NUTRITION] = $description;
		return $changed;
	}
}
