<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidTimestampException;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\TimestampHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Fix the timestamps of a recipe providing a canonical format.
 *
 * The filter works on the `dateCreated`, `dateModified`, and `datePublished` entries, individually.
 *
 * If a timestamp is not present or not able to read, it is set to null.
 * The timestamp is parsed and reformatted as ISO 8601 including a timezone.
 */
class FixTimestampsFilter extends AbstractJSONFilter {
	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	/** @var TimestampHelper */
	private $timestampHelper;

	public function __construct(
		IL10N $l,
		LoggerInterface $logger,
		TimestampHelper $tsHelper
	) {
		$this->l = $l;
		$this->logger = $logger;
		$this->timestampHelper = $tsHelper;
	}

	public function apply(array &$json): bool {
		$changed = false;

		$this->fixTimestamp($json, 'dateCreated', $changed);
		$this->fixTimestamp($json, 'dateModified', $changed);
		$this->fixTimestamp($json, 'datePublished', $changed);

		return $changed;
	}

	private function fixTimestamp(array &$json, string $type, bool &$changed): void {
		if (!isset($json[$type])) {
			$json[$type] = null;
			$changed = true;
			return;
		}

		$orig = $json[$type];
		try {
			$json[$type] = $this->timestampHelper->parseTimestamp($json[$type]);
		} catch (InvalidTimestampException $ex) {
			$json[$type] = null;
		}

		if ($orig !== $json[$type]) {
			$changed = true;
		}
	}
}
