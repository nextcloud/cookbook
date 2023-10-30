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

	/**
	 * Parses the string $duration and checks if it has a valid ISO 8601 duration format. Otherwise throws
	 * InvalidDurationException.
	 *
	 * For reference of ISO 8601, see <a href="https://en.wikipedia.org/wiki/ISO_8601">Wikipedia</a>.
	 * @param string $duration
	 * @return string
	 * @throws InvalidDurationException if $duration does not comply to ISO 8601.
	 */
	private function parseIsoFormat(string $duration): string {
		// P (for period) denotes the duration and must be at the start.
		// The single parts are optional, but the lookahead (?=\d) ensures that there is some time (digit) given.
		// Years, months, and days are improbable for recipes but so what.. ;) That being said: It's not supported.
		$pattern = '/^P(?!$)(\d+Y)?(\d+M)?(\d+W)?(\d+D)?(T(?=\d)(\d+H)?(\d+M)?(\d+S)?)?$/';
		$ret = preg_match($pattern, trim($duration), $matches);

		if ($ret === 1) {
			// $matches[0] is the complete string (like P1Y2M3DT4H5M6S)
			// $matches[1] to $matches[3] is years to days (like 1Y 2M 3D)
			// $matches[5] is the tome part of the string (like T4H5M6S)
			// $matches[6] to $matches[8] is hours to seconds (like 4H 5M 6S)
			$hours = (int)$matches[6];
			$minutes = (int) ($matches[7] ?? 0);
			$seconds = (int) ($matches[8] ?? 0);

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
