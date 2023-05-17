<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use OCA\Cookbook\Exception\InvalidDurationException;
use OCA\Cookbook\Helper\Filter\AbstractJSONFilter;
use OCA\Cookbook\Helper\ISO8601DurationHelper;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

/**
 * Fix the durations of a recipe.
 *
 * The filter works on the prepTime, cookTime and totalTime entries, individually.
 *
 * If a time is not present or not able to read, it is set to null.
 * The time is parsed and reformatted as ISO 8601 duration.
 */
class FixDurationsFilter extends AbstractJSONFilter {
	private const DURATIONS = 'durations';

	/** @var IL10N */
	private $l;

	/** @var LoggerInterface */
	private $logger;

	/** @var ISO8601DurationHelper */
	private $iso8601DurationHelper;

	public function __construct(
		IL10N $l,
		LoggerInterface $logger,
		ISO8601DurationHelper $isoHelper
	) {
		$this->l = $l;
		$this->logger = $logger;
		$this->iso8601DurationHelper = $isoHelper;
	}

	public function apply(array &$json): bool {
		$changed = false;

		$this->fixDate($json, 'prepTime', $changed);
		$this->fixDate($json, 'cookTime', $changed);
		$this->fixDate($json, 'totalTime', $changed);

		return $changed;
	}

	private function fixDate(array &$json, string $type, bool &$changed): void {
		if (!isset($json[$type])) {
			$json[$type] = null;
			$changed = true;
			return;
		}

		$orig = $json[$type];
		try {
			$json[$type] = $this->iso8601DurationHelper->parseDuration($json[$type]);
		} catch (InvalidDurationException $ex) {
			$json[$type] = null;
		}

		if ($orig !== $json[$type]) {
			$changed = true;
		}
	}
}
