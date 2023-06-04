<?php

namespace OCA\Cookbook\Helper;

use OCA\Cookbook\Exception\InvalidDurationException;
use OCP\IL10N;

/**
 * Parser for ISO8601 and similar time spans.
 */
class ISO8601DurationHelper {
	/** @var IL10N */
	private $l;

	public function __construct(IL10N $l) {
		$this->l = $l;
	}

	/**
	 * Parse a string representation of a duration and format as ISO8601 format.
	 *
	 * The output time span is in the form `PT#H#M#S`.
	 *
	 * @param string $duration The duration to parse
	 * @return string The duration in ISO8601 format
	 * @throws InvalidDurationException if the input data could not be parsed successfully.
	 */
	public function parseDuration(string $duration): string {
		try {
			return $this->parseIsoFormat($duration);
		} catch (InvalidDurationException $ex) {
			// We do nothing here. Check the next format
		}

		try {
			return $this->parseHMFormat($duration);
		} catch (InvalidDurationException $ex) {
			// We do nothing here. Check the next format
		}

		// No more formats are available.
		throw new InvalidDurationException($this->l->t('Could not parse duration {duration}', ['duration' => $duration]));
	}

	private function parseIsoFormat(string $duration): string {
		$pattern = '/^PT(\d+)H(?:(\d+)M(?:(\d+)S)?)?$/';
		$ret = preg_match($pattern, trim($duration), $matches);

		if ($ret === 1) {
			$hours = (int)$matches[1];
			$minutes = (int) ($matches[2] ?? 0);
			$seconds = (int) ($matches[3] ?? 0);

			while ($seconds >= 60) {
				$seconds -= 60;
				$minutes += 1;
			}

			while ($minutes >= 60) {
				$minutes -= 60;
				$hours += 1;
			}

			return sprintf('PT%dH%dM%dS', $hours, $minutes, $seconds);
		} else {
			throw new InvalidDurationException('Could not parse as ISO8601 duration.');
		}
	}

	private function parseHMFormat(string $duration): string {
		$pattern = '/^(\d+):(\d{1,2})$/';
		$ret = preg_match($pattern, trim($duration), $matches);

		if ($ret === 1) {
			return sprintf('PT%dH%dM', $matches[1], $matches[2]);
		} else {
			throw new InvalidDurationException('Could not parse as HH:MM duration.');
		}
	}
}
