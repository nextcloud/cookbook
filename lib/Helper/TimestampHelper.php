<?php

namespace OCA\Cookbook\Helper;

use DateTime;
use DateTimeImmutable;
use OCA\Cookbook\Exception\InvalidTimestampException;
use OCP\IL10N;

/**
 * Parser for ISO8601 and other timestamps. Currently only ISO8601 is supported.
 */
class TimestampHelper {
	/** @var IL10N */
	private $l;


	// Output format: Ignore fractions of a second
	public const OUTPUT_FORMAT = 'Y-m-d\TH:i:sP';

	public function __construct(IL10N $l) {
		$this->l = $l;
	}

	/**
	 * Parse a string representation of a timestamp and format according to ISO8601.
	 * For reference, see <a href="https://en.wikipedia.org/wiki/ISO_8601">Wikipedia</a>.
	 *
	 * The output time span is in the form `YYYY-MM-DDThh:mm:ss±hh:mm`, e.g., `2023-11-25T14:25:36+01:00`.
	 * - YYYY - year
	 * - MM - month
	 * - DD - day
	 * - hh - hours
	 * - mm - minutes
	 * - ss - seconds
	 *
	 * The time before the ± defines the local time, the time after ± defines the timezone of the timestamp. In order to
	 * calculate the time in UTC, the value after ± needs to be added/subtracted from the local time.
	 *
	 * @param string $timestamp The timestamp to parse
	 * @return string The timestamp in ISO8601 format
	 * @throws InvalidTimestampException if the input data could not be parsed successfully.
	 */
	public function parseTimestamp(string $timestamp): string {
		// For now, we only support the ISO8601 format because it is required in the schema.org standard
		try {
			return $this->parseIsoFormat($timestamp);
		} catch (InvalidTimestampException) {
			// We do nothing here. Check the next format
		}

		// No more formats are available.
		throw new InvalidTimestampException($this->l->t('Could not parse timestamp {timestamp}', ['timestamp' => $timestamp]));
	}

	/**
	 * Parses the string $timestamp and checks if it has a valid ISO 8601 format. Otherwise, throws
	 * InvalidTimestampException.
	 *
	 * For reference of ISO 8601, see <a href="https://en.wikipedia.org/wiki/ISO_8601">Wikipedia</a>.
	 * @param string $timestamp Timestamp to be parsed.
	 * @return string
	 * @throws InvalidTimestampException if $timestamp does not comply to ISO 8601.
	 */
	private function parseIsoFormat(string $timestamp): string {
		try {
			return $this->parseIsoCalendarDateFormat($timestamp, '-');
		} catch (InvalidTimestampException) { // Check next format
		}
		try {
			return $this->parseIsoCalendarDateFormat($timestamp, '');
		} catch (InvalidTimestampException) { // Check next format
		}
		try {
			return $this->parseIsoWeekDateFormat($timestamp, '-');
		} catch (InvalidTimestampException) { // Check next format
		}

		return $this->parseIsoWeekDateFormat($timestamp, '');
	}

	/**
	 * Parses the string $timestamp and checks if it has a valid ISO 8601 with date as year, month, and day.
	 * Otherwise, throws InvalidTimestampException.
	 *
	 * For reference of ISO 8601, see <a href="https://en.wikipedia.org/wiki/ISO_8601">Wikipedia</a>.
	 * @param string $timestamp Timestamp to be parsed
	 * @param string $dateSeparator Separator to be used between `YYYY`, `mm`, and `dd`.
	 * @return string
	 * @throws InvalidTimestampException if $timestamp does not comply to ISO 8601 with week and weekday.
	 */
	private function parseIsoCalendarDateFormat(string $timestamp, string $dateSeparator = '-'): string {
		$date = "Y".$dateSeparator."m".$dateSeparator."d";

		return $this->parseIsoTimestampWithTimeFormats($timestamp, $date);
	}

	/**
	 * Parses the string $timestamp and checks if it has a valid ISO 8601 format with date defined as week and weekday.
	 * Otherwise, throws InvalidTimestampException.
	 *
	 * For reference of ISO 8601, see <a href="https://en.wikipedia.org/wiki/ISO_8601">Wikipedia</a>.
	 * @param string $timestamp Timestamp to be parsed
	 * @param string $dateSeparator Separator to be used between `YYYY`, `mm`, and `dd`.
	 * @return string
	 * @throws InvalidTimestampException if $timestamp does not comply to ISO 8601 with week and weekday.
	 */
	private function parseIsoWeekDateFormat(string $timestamp, string $dateSeparator = '-'): string {
		$pattern = "/^(?!$)(\d\d\d\d)" . $dateSeparator . "W(\d\d)" . $dateSeparator . "(\d)T.*$/";
		$ret = preg_match($pattern, trim($timestamp), $matches);

		if ($ret === 1) {
			// Convert week format to calendar format for date
			$tmpDate = new DateTime('midnight');
			// $matches[0] is the complete string
			// $matches[1] to $matches[3] is year, week, weekday
			$tmpDate->setISODate((int)$matches[1], (int)$matches[2], (int)$matches[3]);
			$tmpDateString = $tmpDate->format('Y-m-d\TH:i:sP');

			// Combine converted date with original time
			$timePartOfTimestamp = substr($timestamp, 8 + 2 * mb_strlen($dateSeparator));
			$datePartOfConvertedTimestamp = substr($tmpDateString, 0, 10);
			$updatedTimestamp = $datePartOfConvertedTimestamp . $timePartOfTimestamp;

			// Parse complete date including time
			$dateFormat = 'Y-m-d';
			return $this->parseIsoTimestampWithTimeFormats($updatedTimestamp, $dateFormat);
		}

		throw new InvalidTimestampException($this->l->t('Could not parse timestamp {timestamp}', ['timestamp' => $timestamp]));
	}


	/**
	 * Parses the string $timestamp and checks if it has a valid ISO 8601. Uses the date format given by $dateFormat
	 * and checks several allowed formats for the time.
	 *
	 * For reference of ISO 8601, see <a href="https://en.wikipedia.org/wiki/ISO_8601">Wikipedia</a>.
	 * @param string $timestamp Timestamp to be parsed
	 * @param string $dateFormat Format to be used for the date portion
	 * @return string
	 * @throws InvalidTimestampException if $timestamp does not comply to any of the checked formats allowed by ISO 8601.
	 */
	private function parseIsoTimestampWithTimeFormats(string $timestamp, string $dateFormat): string {
		// Try parsing timestamp without milliseconds
		$dt = DateTimeImmutable::createFromFormat($dateFormat . "\\TH:i:sP", $timestamp);
		if($dt) {
			return $dt->format(self::OUTPUT_FORMAT);
		}

		// Try parsing timestamp with dot-separated milliseconds
		$dt = DateTimeImmutable::createFromFormat($dateFormat . "\\TH:i:s.vP", $timestamp);
		if($dt) {
			return $dt->format(self::OUTPUT_FORMAT);
		}

		// Try parsing timestamp with comma-separated milliseconds
		$dt = DateTimeImmutable::createFromFormat($dateFormat . "\\TH:i:s,vP", $timestamp);
		if($dt) {
			return $dt->format(self::OUTPUT_FORMAT);
		}
		throw new InvalidTimestampException($this->l->t('Could not parse timestamp {timestamp}', ['timestamp' => $timestamp]));
	}

}
