<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Helper\TextCleanupHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Fix the description of a recipe.
 *
 * The description must be present in all cases.
 * If is is not present, this filter will insert the empty string instead.
 *
 * Apart from that the description is cleaned from malicious chars.
 */
class FixDescriptionFilter extends AbstractJSONFilter {
	private const DESCRIPTION = 'description';

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	/** @var TextCleanupHelper */
	private $textCleaner;

	public function __construct(
		IL10N $l,
		LoggerInterface $logger,
		TextCleanupHelper $textCleanupHelper
	) {
		$this->l = $l;
		$this->logger = $logger;
		$this->textCleaner = $textCleanupHelper;
	}

	public function apply(array &$json): bool {
		if (!isset($json[self::DESCRIPTION])) {
			$json[self::DESCRIPTION] = '';
			return true;
		}

		$description = $this->textCleaner->cleanUp($json[self::DESCRIPTION], false);
		$description = trim($description);

		$changed = $description !== $json[self::DESCRIPTION];
		$json[self::DESCRIPTION] = $description;
		return $changed;
	}
}
