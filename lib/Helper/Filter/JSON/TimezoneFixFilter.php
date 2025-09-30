<?php

namespace OCA\Cookbook\Helper\Filter\JSON;

use DateTime;
use DateTimeZone;
use Psr\Log\LoggerInterface;

/**
 * Fix the timestamp of the dates created and modified for recipe stubs to have a timezone
 *
 * This expects ISO conforming time data as input
 */
class TimezoneFixFilter extends AbstractJSONFilter {
	/** @var LoggerInterface */
	private $logger;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	#[\Override]
	public function apply(array &$json): bool {
		$changed = false;
		foreach (['dateCreated', 'dateModified'] as $key) {
			if (isset($json[$key]) && $json[$key]) {
				$json[$key] = $this->handleTimestamp($json[$key], $changed);
			}
		}

		return $changed;
	}

	private function handleTimestamp(string $value, bool &$changed): string {
		$pattern = '/^([0-9]{4}-[0-9]{2}-[0-9]{2})T([0-9]{1,2}:[0-9]{2}:[0-9]{2}(?:[.,][0-9]+)?)(\+[0-9]{2}:?[0-9]{2})/';
		$match = preg_match($pattern, $value, $matches);

		if ($match == 1) {
			return $value;
		}

		try {
			$defaultTimezone = date_default_timezone_get();
		} catch (\Exception $ex) {
			$this->logger->error('Cannot get the default timezone of server.');
			return $value;
		}

		$serverTimeZone = new DateTimeZone($defaultTimezone);
		$now = new DateTime('now', $serverTimeZone);
		$offsetSec = $serverTimeZone->getOffset($now);
		$offsetHour = (int)($offsetSec / 3600);

		$changed = true;

		return sprintf('%s%+05d', $value, $offsetHour * 100);
	}

}
